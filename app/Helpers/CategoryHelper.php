<?php

if (!function_exists('isCategorySelected')) {
    /**
     * Check if a category is selected for a product
     *
     * @param int $categoryId
     * @param array $selectedCategories
     * @return bool
     */
    function isCategorySelected($categoryId, $selectedCategories) {
        return isset($selectedCategories[$categoryId]);
    }
}

if (!function_exists('isSubcategorySelected')) {
    /**
     * Check if a subcategory is selected for a product
     *
     * @param int $categoryId
     * @param int $subcategoryId
     * @param array $selectedCategories
     * @return bool
     */
    function isSubcategorySelected($categoryId, $subcategoryId, $selectedCategories) {
        if (!isset($selectedCategories[$categoryId])) {
            return false;
        }
        
        return in_array($subcategoryId, $selectedCategories[$categoryId]['subcategory_ids'] ?? []);
    }
}