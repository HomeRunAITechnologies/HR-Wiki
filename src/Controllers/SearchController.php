<?php

declare(strict_types=1);

namespace WikiApp\Controllers;

use WikiApp\Lib\View;
use WikiApp\Models\Page;
use WikiApp\Models\Category;

class SearchController
{
    /**
     * Handle search queries and display results.
     */
    public function index(): void
    {
        $query = trim($_GET['q'] ?? '');
        $searchResults = [];
        $categoryResults = [];

        if (!empty($query)) {
            // Search pages by title and content
            $pageResults = Page::getAllObjects(
                'id',
                [
                    ['title', null, 'LIKE', '%' . $query . '%'],
                    ['content', null, 'LIKE', '%' . $query . '%']
                ],
                'OR', // Match title OR content
                true
            );

            // Filter out duplicates if both title and content match the same page
            $processedPageIds = [];
            foreach ($pageResults as $page) {
                if (!in_array((int)$page->id, $processedPageIds)) {
                    $searchResults[] = $page;
                    $processedPageIds[] = (int)$page->id;
                }
            }
            
            // Search categories by name
            $categoryResults = Category::getAllObjects(
                'id',
                [['name', null, 'LIKE', '%' . $query . '%']],
                'AND',
                true
            );
        }

        View::render('search/index', [
            'title' => 'Search Results for "' . htmlspecialchars($query) . '"',
            'query' => $query,
            'pageResults' => $searchResults,
            'categoryResults' => $categoryResults,
        ]);
    }
}
