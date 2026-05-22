<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request) { $q = Product::with('category'); if ($request->filled('search')) $q->where('name', 'like', "%{$request->search}%"); if ($request->filled('category')) $q->where('category_id', $request->category); if ($request->filled('type')) $q->where('type', $request->type); return view('products.index', ['products' => $q->latest()->paginate(25), 'categories' => ProductCategory::where('is_active', true)->get()]); }
    public function create() { return view('products.create', ['categories' => ProductCategory::where('is_active', true)->get()]); }
    public function store(Request $request) { $v = $request->validate(['name' => 'required|string|max:255', 'category_id' => 'nullable|exists:product_categories,id', 'sku' => 'nullable|string|unique:products', 'hsn_sac_code' => 'nullable|string', 'description' => 'nullable|string', 'unit' => 'nullable|string', 'cost_price' => 'required|numeric|min:0', 'selling_price' => 'required|numeric|min:0', 'tax_percentage' => 'nullable|numeric', 'type' => 'required|in:product,service', 'image' => 'nullable|image|max:2048']); if ($request->hasFile('image')) $v['image'] = $request->file('image')->store('products', 'public'); Product::create($v); return redirect()->route('admin.products.index')->with('success', 'Product created.'); }
    public function show(Product $product) { return view('products.show', compact('product')); }
    public function edit(Product $product) { return view('products.edit', ['product' => $product, 'categories' => ProductCategory::where('is_active', true)->get()]); }
    public function update(Request $request, Product $product) { $v = $request->validate(['name' => 'required|string', 'category_id' => 'nullable|exists:product_categories,id', 'sku' => 'nullable|string|unique:products,sku,' . $product->id, 'cost_price' => 'required|numeric', 'selling_price' => 'required|numeric', 'type' => 'required|in:product,service', 'is_active' => 'boolean']); if ($request->hasFile('image')) $v['image'] = $request->file('image')->store('products', 'public'); $product->update($v); return redirect()->route('admin.products.index')->with('success', 'Updated.'); }
    public function destroy(Product $product) { $product->delete(); return redirect()->route('admin.products.index')->with('success', 'Deleted.'); }
    public function categories() { return view('products.categories', ['categories' => ProductCategory::withCount('products')->get()]); }
    public function storeCategory(Request $request) { $request->validate(['name' => 'required|string']); ProductCategory::create(['name' => $request->name, 'slug' => Str::slug($request->name), 'description' => $request->description]); return back()->with('success', 'Category created.'); }
}
