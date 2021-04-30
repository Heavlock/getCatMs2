<?php

/**Готовим и отправляем query запрос*/

$sql = 'SELECT price,
       products.id as products_id,
       image,
       thumb,
       content.id as content_id, 
       content.pagetitle as content_pagetitle,
       content.parent as content_parent,
       parent.id as parent_id, 
       parent.pagetitle as parent_pagetitle, 
       parent.uri as parent_uri     
from modx_ms2_products as products
left join modx_site_content as content on content.id = products.id
left join modx_site_content as parent on parent.id = content.parent
order by parent_pagetitle ASC, price ASC
';
$query = $modx->query($sql);
$products = $query->fetchAll(PDO::FETCH_ASSOC);

/**Получаем миниатюру товара и минимальную цену для отображения*/

foreach ($products as $product) {
    static $resultArr = [];
    if (
        empty($resultArr[$product['parent_pagetitle']]['price'][0][0])
        && empty($resultArr[$product['parent_pagetitle']]['uri'][0])
    ) {
        $resultArr[$product['parent_pagetitle']]['price'][] = [$product['price']];
        $resultArr[$product['parent_pagetitle']]['uri'] = [$product['parent_uri']];
    }
    if (!empty($product['thumb'])) {
        $resultArr[$product['parent_pagetitle']]['thumb'] = [$product['thumb']];
    }
}
/**Создаем плейсхолдер с заголовком категории*/
$categorys = array_count_values(array_column($products, 'parent_pagetitle'));
$modx->setPlaceholder('categorys', $categorys);

/**Выводим елементы с категориями через цикл*/
foreach ($categorys as $categoryName => $count) {
    if (empty($resultArr[$categoryName]['thumb'][0])) {
        $resultArr[$categoryName]['thumb'][0] = '/assets/components/minishop2/img/web/ms2_small.png';
    }
    echo '<div class="featured-categories-wrap">
                    <div class="single-featured-categories mb-25">
                        <div class="featured-categories-content">
                            <h3><a href="/' . $resultArr[$categoryName]['uri'][0] . '">' . $categoryName . '</a></h3>
                            <p>' . $count . ' товаров от ' . $resultArr[$categoryName]['price'][0][0] . ' ' . $modx->lexicon('ms2_frontend_currency') . '</p>
                            <ul class="sub-cat">
                                <li>Vintage</li>
                                <li>Bohemian</li>
                                <li>Chic Fashion</li>
                                <li>Sophisticated</li>
                            </ul>
                            <div class="btn-style-3 btn-hover-2">
                                <a class="animated bs3-gray-text bs3-gray-bg bs3-ptb-3 bs3-border-radius ptb-2-theme-hover font-dec" href="product-details.html">Shop now</a>
                            </div>
                        </div>
                        <div class="featured-categories-img d-none d-sm-block">
                            <a href="/' . $resultArr[$categoryName]['uri'][0] . '"><img src="' . $resultArr[$categoryName]['thumb'][0] . '" alt="categories"></a>
                        </div>
                    </div>
                </div>';
}
