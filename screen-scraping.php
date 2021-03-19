<?php
include_once 'screen_scraping.php';
/**
 * Loop through this data
 */
$data = Page_Scrapping::get_scraped_data("test");
print_r($data);
