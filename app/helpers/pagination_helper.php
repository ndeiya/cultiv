<?php
/**
 * Pagination Helper
 * Utilities for paginating database results.
 */

/**
 * Paginate a query result.
 *
 * @param PDO    $db       The PDO instance
 * @param string $sql      The base SQL query
 * @param array  $params   The query parameters
 * @param int    $page     The current page (1-based)
 * @param int    $perPage  Results per page
 * @return array ['data' => [], 'total' => N, 'page' => N, 'per_page' => N, 'total_pages' => N]
 */
function paginate(PDO $db, string $sql, array $params = [], int $page = 1, int $perPage = 20): array
{
    // Ensure positive integers
    $page = max(1, (int)$page);
    $perPage = max(1, (int)$perPage);
    
    // Count total records
    // Remove ORDER BY if present to simplify the count query
    $countSql = preg_replace('/ORDER BY.*/i', '', $sql);
    $countSql = "SELECT COUNT(*) FROM (" . $countSql . ") as count_table";
    
    $stmtCount = $db->prepare($countSql);
    $stmtCount->execute($params);
    $total = (int)$stmtCount->fetchColumn();
    
    // Calculate offset
    $offset = ($page - 1) * $perPage;
    $totalPages = ceil($total / $perPage);
    
    // Add LIMIT and OFFSET to original query
    $sql .= " LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($sql);
    
    // Bind all original params
    foreach ($params as $key => $value) {
        if (is_int($key)) {
            $stmt->bindValue($key + 1, $value);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    
    // Bind limit and offset as integers (PDO::PARAM_INT is crucial for LIMIT)
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'data'        => $data,
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => $totalPages,
    ];
}
