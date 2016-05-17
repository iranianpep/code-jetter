<?php

    /** @var CodeJetter\core\layout\blocks\Pager $this */
    $totalPagesNo = $this->getTotalPagesNo();
    $start = $this->getStart();
    $limit = $this->getLimit();
    $currentPage = $this->getCurrentPage();
    $pagerLimits = $this->getPagerLimits();

    // determine the path
    $path = !empty($this->getBasePath()) ? $this->getBasePath() : $this->getPath();
    $queryString = $this->getQueryString();

    /**
     * Start generating pages html
     */
    $pagesHtml = '<ul class="pagination">';
for ($i = 1; $i <= $totalPagesNo; $i++) {
    $active = $i === $currentPage ? 'active' : '';
    $fullPath = $this->getFullPathByPageNumber($i);
    $pagesHtml .= "<li class='{$active}'><a href='{$fullPath}'>{$i}</a></li>";
}

    $pagesHtml .= '</ul>';
    /**
     * Finish generating pages html
     */

    /**
     * Start generating per page html
     */
    $perPageHtml = "<select id='per-page' class='form-control' onchange=\"redirectToPage('{$path}', {$currentPage}, this.value, '{$queryString}');\">";

foreach ($pagerLimits as $pagerLimit) {
    $selected = $pagerLimit === $limit ? 'selected' : '';
    $perPageHtml .= "<option value='{$pagerLimit}' {$selected}>{$pagerLimit}</option>";
}

    $perPageHtml .= '</select>';
    /**
     * Finish generating per page html
     */

    /**
     * Final html
     */
    $html = "<div class='row'>
<div class='col-md-10'>
        {$pagesHtml}
    </div>

        <div class='form-horizontal pagination col-md-2'>
            <div class='form-group'>
                <label for='per-page' class='col-md-6 control-label'>Per page:</label>
                <div class='col-md-6'>
                    {$perPageHtml}
                </div>
            </div>
        </div>

</div>";

    return $html;
