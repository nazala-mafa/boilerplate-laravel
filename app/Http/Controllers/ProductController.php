<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\FileUpload;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function __construct(
        private Product $product,
        private FileUpload $fileUpload,
    ) { }

    public function index()
    {
        $sort = [
            // TL;DR: "sort_field" => "sort_direction"
            "updated_at" => 'desc',
            "id" => "desc",
        ];

        $datas = $this->product
            ->query()
            ->with('user')
            ->when(request()->query('search'), function($query, $search) {
                $query->where('name', 'LIKE', "%$search%");
                $query->orWhereRelation('user', 'name', 'LIKE', "%$search%");
            })
            ->when($sort, function($query, $sort) {
                foreach ($sort as $sortField => $sortDirection) {
                    $query->orderBy($sortField, $sortDirection);
                }
            })
            ->paginate(request()->query('perPage', 10))
            ->withQueryString()
            ->onEachSide(3)
            ->withPath('/product');

        $datas->transform(function($item) {
            $item->formatted_created_at = $item->created_at->translatedFormat('l, d F Y');
            $item->formatted_updated_at = $item->updated_at->translatedFormat('l, d F Y');
            unset($item->created_at, $item->updated_at);
            return $item;
        });
        
        return $datas;
    }

    public function store(StoreProductRequest $request)
    {
        DB::beginTransaction();

        try {
            $product = $this->product->create($request->only([
                "user_id",
                "name",
                "description",
                "price",
                "image_url",
            ]));

            $this->fileUpload->updateIsUsed($product, $product->image_url);

            DB::commit();

            return response()->json([
                "message" => "The product was successfully created.",
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error($th);

            return response()->json([
                "message" => "An error occurred while creating the product, contact admin."
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('user');
        return response()->json(compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        DB::beginTransaction();

        try {
            $product->update($request->only([
                "user_id",
                "name",
                "description",
                "price",
                "image_url",
            ]));

            $this->fileUpload->updateIsUsed($product, $product->image_url);

            DB::commit();

            return response()->json([
                "message" => "The product was successfully updated.",
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error($th);

            return response()->json([
                "message" => "An error occurred while updating the product, contact admin."
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();

        try {
            $this->fileUpload->updateIsUsed($product, $product->image_url, false);
            
            $product->delete();

            DB::commit();

            return response()->json([
                "message" => "The product was successfully deleted.",
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error($th);

            return response()->json([
                "message" => "An error occurred while deleting the product, contact admin."
            ], 500);
        }
    }
}
