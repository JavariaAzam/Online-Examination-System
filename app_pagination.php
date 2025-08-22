<?php
function paginate(int $page, int $perPage, int $total): array {
  $page = max(1,$page);
  $pages = max(1, (int)ceil($total / $perPage));
  $page = min($page,$pages);
  $offset = ($page-1)*$perPage;
  return [$offset, $perPage, $page, $pages];
}
