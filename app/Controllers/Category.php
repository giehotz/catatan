<?php

namespace App\Controllers;

use App\Models\IncomeCategoryModel;
use App\Models\ExpenseCategoryModel;

class Category extends BaseController
{
    protected IncomeCategoryModel $incomeCategoryModel;
    protected ExpenseCategoryModel $expenseCategoryModel;

    public function __construct()
    {
        $this->incomeCategoryModel = new IncomeCategoryModel();
        $this->expenseCategoryModel = new ExpenseCategoryModel();
    }

    public function index()
    {
        $userId = auth()->id();

        // 1. Seed defaults if empty
        $this->seedDefaultCategories($userId);

        // 2. Fetch all categories
        $incomes = $this->incomeCategoryModel->where('user_id', $userId)->findAll();
        $expenses = $this->expenseCategoryModel->where('user_id', $userId)->findAll();

        // 3. Process Hierarchies
        $incomeTree = $this->buildCategoryTree($incomes);
        $expenseTree = $this->buildCategoryTree($expenses);

        return view('user/categories/index', [
            'title'        => 'Kelola Kategori',
            'incomeTree'   => $incomeTree,
            'expenseTree'  => $expenseTree,
            'rawIncomes'   => $incomes,
            'rawExpenses'  => $expenses,
        ]);
    }

    public function create()
    {
        $rules = [
            'type'      => 'required|in_list[income,expense]',
            'name'      => 'required|min_length[2]|max_length[100]',
            'parent_id' => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = auth()->id();
        $type = $this->request->getPost('type');
        $parentId = $this->request->getPost('parent_id') ?: null;

        if ($parentId !== null) {
            // Verify parent category belongs to this user
            if ($type === 'income') {
                $parentExists = $this->incomeCategoryModel->where('id', $parentId)->where('user_id', $userId)->first();
            } else {
                $parentExists = $this->expenseCategoryModel->where('id', $parentId)->where('user_id', $userId)->first();
            }

            if (!$parentExists) {
                return redirect()->back()->withInput()->with('error', 'Kategori utama yang dipilih tidak valid.');
            }
        }

        $data = [
            'user_id'   => $userId,
            'name'      => esc($this->request->getPost('name')),
            'parent_id' => $parentId,
        ];

        if ($type === 'income') {
            $this->incomeCategoryModel->insert($data);
        } else {
            $this->expenseCategoryModel->insert($data);
        }

        return redirect()->to('/category')->with('message', 'Kategori berhasil ditambahkan.');
    }

    public function delete(string $type, int $id)
    {
        $userId = auth()->id();

        if ($type === 'income') {
            $category = $this->incomeCategoryModel->where('id', $id)->where('user_id', $userId)->first();
            if (!$category) {
                return redirect()->to('/category')->with('error', 'Kategori tidak ditemukan.');
            }
            $this->incomeCategoryModel->delete($id);
        } elseif ($type === 'expense') {
            $category = $this->expenseCategoryModel->where('id', $id)->where('user_id', $userId)->first();
            if (!$category) {
                return redirect()->to('/category')->with('error', 'Kategori tidak ditemukan.');
            }
            $this->expenseCategoryModel->delete($id);
        } else {
            return redirect()->to('/category')->with('error', 'Parameter tipe tidak valid.');
        }

        return redirect()->to('/category')->with('message', 'Kategori berhasil dihapus.');
    }

    private function buildCategoryTree(array $categories): array
    {
        $parents = [];
        $children = [];

        foreach ($categories as $cat) {
            if ($cat['parent_id'] === null) {
                $parents[$cat['id']] = $cat;
                $parents[$cat['id']]['children'] = [];
            } else {
                $children[] = $cat;
            }
        }

        foreach ($children as $child) {
            $pId = $child['parent_id'];
            if (isset($parents[$pId])) {
                $parents[$pId]['children'][] = $child;
            } else {
                // If parent isn't found (or deleted), treat it as a main category
                $parents[$child['id']] = $child;
                $parents[$child['id']]['children'] = [];
            }
        }

        return array_values($parents);
    }

    private function seedDefaultCategories(int $userId)
    {
        // Income Default Seeding
        $incomeCount = $this->incomeCategoryModel->where('user_id', $userId)->countAllResults();
        if ($incomeCount === 0) {
            $defaultIncomes = ['Gaji Pokok', 'Freelance', 'Investasi', 'Lainnya'];
            foreach ($defaultIncomes as $name) {
                $this->incomeCategoryModel->insert([
                    'user_id' => $userId,
                    'name'    => $name
                ]);
            }
        }

        // Expense Default Seeding
        $expenseCount = $this->expenseCategoryModel->where('user_id', $userId)->countAllResults();
        if ($expenseCount === 0) {
            $defaultExpenses = ['Makanan & Minuman', 'Belanja Bulanan', 'Transportasi', 'Utilitas & Tagihan', 'Hiburan', 'Kesehatan', 'Lainnya'];
            foreach ($defaultExpenses as $name) {
                $this->expenseCategoryModel->insert([
                    'user_id' => $userId,
                    'name'    => $name
                ]);
            }
        }
    }
}
