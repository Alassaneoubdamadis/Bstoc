<?php

namespace App\Imports;

use App\Models\BaseUnit;
use App\Models\Brand;
use App\Models\MainProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ProductImport implements ToCollection, WithChunkReading, WithStartRow, WithValidation
{
    public function collection(Collection $rows)
    {
        $collection = $rows->toArray();
        ini_set('max_execution_time', 36000000);
        foreach ($collection as $key => $row) {
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }
            try {
                DB::beginTransaction();

                $taxType = null;
                $rowLabel = $key + 2;

                $productName = Product::whereName($row[0])->exists();
                if ($productName) {
                    throw new UnprocessableEntityHttpException('Produit déjà existant : '.$row[0].' (ligne '.$rowLabel.').');
                }
                $productCode = Product::where('code', $row[1])->exists();
                if ($productCode) {
                    throw new UnprocessableEntityHttpException('Code produit déjà existant : '.$row[1].' (ligne '.$rowLabel.').');
                }

                $productCategory = ProductCategory::whereName($row[2])->first();
                $brandName = trim((string) ($row[3] ?? ''));
                $brand = $brandName !== '' ? Brand::whereName($brandName)->first() : null;

                $baseUnitName = strtolower(trim((string) $row[7]));
                $baseUnit = BaseUnit::whereRaw('LOWER(name) = ?', [$baseUnitName])->first();

                if (! $baseUnit) {
                    throw new UnprocessableEntityHttpException(
                        'Unité de base introuvable : '.$row[7].' (ligne '.$rowLabel.'). Utilisez piece, meter ou kilogram.'
                    );
                }

                $productUnitId = $baseUnit->id;
                $saleUnit = $this->resolveUnit(trim((string) $row[8]), $productUnitId);
                $purchaseUnit = $this->resolveUnit(trim((string) $row[9]), $productUnitId);

                if ($productCategory) {
                    $productCategoryId = $productCategory->id;
                } else {
                    $productCategory = ProductCategory::create(['name' => $row[2]]);
                    $productCategoryId = $productCategory->id;
                }

                if ($brand) {
                    $brandId = $brand->id;
                } elseif ($brandName !== '') {
                    $brand = Brand::create(['name' => $brandName]);
                    $brandId = $brand->id;
                } else {
                    $brand = Brand::firstOrCreate(['name' => 'Sans marque']);
                    $brandId = $brand->id;
                }

                $barcodeRaw = strtoupper(trim((string) $row[4]));
                if ($barcodeRaw === 'CODE128') {
                    $barcodeSymbol = 1;
                } elseif ($barcodeRaw === 'CODE39') {
                    $barcodeSymbol = 2;
                } else {
                    throw new UnprocessableEntityHttpException(
                        'Symbole code-barres invalide : '.$row[4].' (ligne '.$rowLabel.'). Utilisez CODE128 ou CODE39.'
                    );
                }

                if (strtolower(trim((string) $row[12])) == 'exclusive') {
                    $taxType = 1;
                } elseif (strtolower(trim((string) $row[12])) == 'inclusive') {
                    $taxType = 2;
                } else {
                    throw new UnprocessableEntityHttpException(
                        'Type de taxe invalide : '.$row[12].' (ligne '.$rowLabel.'). Utilisez EXCLUSIVE ou INCLUSIVE.'
                    );
                }

                $mainProduct = MainProduct::create([
                    'name' => $row[0],
                    'code' => (string) $row[1],
                    'product_unit' => $productUnitId,
                    'product_type' => MainProduct::SINGLE_PRODUCT,
                ]);

                $productData = [
                    'name' => $row[0],
                    'code' => (string) $row[1],
                    'product_code' => (string) $row[1],
                    'product_category_id' => $productCategoryId,
                    'brand_id' => $brandId,
                    'barcode_symbol' => $barcodeSymbol,
                    'product_cost' => $row[5],
                    'product_price' => $row[6],
                    'product_unit' => $productUnitId,
                    'sale_unit' => $saleUnit->id,
                    'purchase_unit' => $purchaseUnit->id,
                    'stock_alert' => isset($row[10]) && $row[10] !== '' ? $row[10] : null,
                    'order_tax' => isset($row[11]) && $row[11] !== '' ? $row[11] : null,
                    'tax_type' => $taxType,
                    'notes' => isset($row[13]) ? $row[13] : null,
                    'main_product_id' => $mainProduct->id,
                ];

                $product = Product::create($productData);

                $reference_code = 'PR_'.$product->id;

                if (! empty($row[14]) && ! empty($row[15]) && ! empty($row[16])) {
                    $purchaseStock = [
                        'warehouse' => $row[14],
                        'supplier' => $row[15],
                        'quantity' => $row[16],
                    ];
                    $warehouse = Warehouse::whereRaw('LOWER(name) = ?', [strtolower($purchaseStock['warehouse'])])->first();
                    $supplier = Supplier::whereRaw('LOWER(name) = ?', [strtolower($purchaseStock['supplier'])])->first();

                    if ($warehouse && $supplier) {
                        manageStock($warehouse->id, $product->id, $purchaseStock['quantity']);
                        $status = strtolower(trim((string) ($row[17] ?? '')));
                        if ($status == 'received') {
                            $status = 1;
                        } elseif ($status == 'ordered') {
                            $status = 3;
                        } else {
                            $status = 2;
                        }

                        $purchaseInputArray = [
                            'supplier_id' => $supplier->id,
                            'warehouse_id' => $warehouse->id,
                            'date' => Carbon::now()->format('Y-m-d'),
                            'status' => $status,
                        ];

                        $purchase = Purchase::create($purchaseInputArray);

                        $purchaseItemInputs = [
                            'purchase_id' => $purchase->id,
                            'product_id' => $product->id,
                            'product_cost' => $product->product_cost,
                            'net_unit_cost' => $product->product_cost,
                            'tax_type' => $product->tax_type,
                            'tax_value' => $product->order_tax,
                            'tax_amount' => 0,
                            'discount_type' => Purchase::FIXED,
                            'discount_value' => 0,
                            'discount_amount' => 0,
                            'purchase_unit' => $product->purchase_unit,
                            'quantity' => $purchaseStock['quantity'],
                            'sub_total' => $product->product_cost * $purchaseStock['quantity'],
                        ];

                        PurchaseItem::create($purchaseItemInputs);

                        $purchase->update([
                            'reference_code' => getSettingValue('purchase_code').'_111'.$purchase->id,
                            'grand_total' => $purchaseItemInputs['sub_total'],
                        ]);
                    }
                }

                $barcodeType = null;
                $generator = new BarcodeGeneratorPNG();
                switch ($barcodeSymbol) {
                    case Product::CODE128:
                        $barcodeType = $generator::TYPE_CODE_128;
                        break;
                    case Product::CODE39:
                        $barcodeType = $generator::TYPE_CODE_39;
                        break;
                    case Product::EAN8:
                        $barcodeType = $generator::TYPE_EAN_8;
                        break;
                    case Product::EAN13:
                        $barcodeType = $generator::TYPE_EAN_13;
                        break;
                    case Product::UPC:
                        $barcodeType = $generator::TYPE_UPC_A;
                        break;
                }

                try {
                    Storage::disk(config('app.media_disc'))->put(
                        'product_barcode/barcode-'.$reference_code.'.png',
                        $generator->getBarcode($row[1], $barcodeType, 4, 70)
                    );
                } catch (\Throwable $barcodeError) {
                    Log::warning('Barcode non généré pour '.$reference_code.': '.$barcodeError->getMessage());
                }

                DB::commit();
            } catch (UnprocessableEntityHttpException $e) {
                DB::rollBack();
                throw $e;
            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Product import row '.($key + 2).': '.$e->getMessage());
                throw new UnprocessableEntityHttpException(
                    'Import impossible (ligne '.($key + 2).') : '.$e->getMessage()
                );
            }
        }
    }

    /**
     * Trouve l'unité du magasin, ou la crée si elle manque (cas fréquent après provision SaaS).
     */
    private function resolveUnit(string $name, int $baseUnitId): Unit
    {
        $name = trim($name);
        if ($name === '') {
            throw new UnprocessableEntityHttpException('Unité de vente/achat manquante.');
        }

        $companyId = current_company_id();
        $needle = strtolower($name);

        // 1) Unité du magasin (sans exiger base_unit : l'index unique est sur le nom).
        $unit = Unit::withoutGlobalScopes()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->whereRaw('LOWER(name) = ?', [$needle])
            ->first();

        if ($unit) {
            if ((int) $unit->base_unit !== $baseUnitId) {
                $unit->base_unit = $baseUnitId;
                $unit->save();
            }

            return $unit;
        }

        // 2) Unité legacy (company_id null) réutilisable.
        $legacy = Unit::withoutGlobalScopes()
            ->whereNull('company_id')
            ->whereRaw('LOWER(name) = ?', [$needle])
            ->first();

        if ($legacy) {
            $legacy->company_id = $companyId;
            $legacy->base_unit = $baseUnitId;
            $legacy->save();

            return $legacy;
        }

        // 3) Création — si collision unique (autre magasin / casse TiDB), on récupère.
        try {
            return Unit::withoutGlobalScopes()->create([
                'name' => $name,
                'short_name' => strtolower(substr($name, 0, 3)),
                'base_unit' => $baseUnitId,
                'company_id' => $companyId,
            ]);
        } catch (\Throwable $e) {
            $existing = Unit::withoutGlobalScopes()
                ->whereRaw('LOWER(name) = ?', [$needle])
                ->first();
            if ($existing) {
                if ($companyId && empty($existing->company_id)) {
                    $existing->company_id = $companyId;
                    $existing->save();
                }

                return $existing;
            }

            throw new UnprocessableEntityHttpException(
                'Unité introuvable : '.$name.'. Créez-la dans Unités (piece, meter ou kilogram).'
            );
        }
    }

    public function chunkSize(): int
    {
        return 1;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            '0' => 'required',
            '1' => 'required',
            '2' => 'required',
            '3' => 'nullable',
            '4' => 'required',
            '5' => 'required|numeric',
            '6' => 'required|numeric',
            '7' => 'required',
            '8' => 'required',
            '9' => 'required',
            '10' => 'nullable|numeric',
            '11' => 'nullable|numeric',
            '12' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Le nom est obligatoire',
            '1.required' => 'Le code est obligatoire',
            '2.required' => 'La catégorie est obligatoire',
            '4.required' => 'Le symbole code-barres est obligatoire (CODE128 ou CODE39)',
            '5.required' => 'Le coût produit est obligatoire',
            '5.numeric' => 'Le coût produit doit être un nombre',
            '6.required' => 'Le prix produit est obligatoire',
            '6.numeric' => 'Le prix produit doit être un nombre',
            '7.required' => 'L’unité produit est obligatoire (piece, meter ou kilogram)',
            '8.required' => 'L’unité de vente est obligatoire',
            '9.required' => 'L’unité d’achat est obligatoire',
            '10.numeric' => 'L’alerte stock doit être un nombre',
            '11.numeric' => 'La taxe doit être un nombre',
            '12.required' => 'Le type de taxe est obligatoire (EXCLUSIVE ou INCLUSIVE)',
        ];
    }
}
