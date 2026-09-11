<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): View
    {
        $products = Product::with(['category', 'brand', 'units'])
            ->search($request->search)
            ->category($request->category_id)
            ->brand($request->brand_id)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    public function create(): View
    {
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();

        return view('products.create', compact('categories', 'brands'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->productService->createProduct(
            $request->validated(),
            $request->file('image')
        );

        return redirect()->route('products.index')->with('success', 'Produk baru dan satuan kemasan berhasil disimpan.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'brand', 'units']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $product->load('units');
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->updateProduct(
            $product,
            $request->validated(),
            $request->file('image')
        );

        return redirect()->route('products.index')->with('success', 'Data produk dan satuan berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->deleteProduct($product);

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus (Soft Delete).');
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $this->productService->toggleStatus($product);

        $statusLabel = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Status produk berhasil {$statusLabel}.");
    }
}
