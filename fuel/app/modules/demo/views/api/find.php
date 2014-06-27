<?php Debug::$js_toggle_open = true; ?>

<h2>/api_v1_master_goods/search(limit=5)</h2>
<table class="table">
	<tr>
		<th>#</th>
		<th>商品コード</th>
		<th>商品名</th>
		<th>在庫数</th>
		<th>サプライヤー名</th>
	</tr>
	<?php foreach($products['data'] as $i => $product): ?>
		<tr>
			<td><?= $i ?></td>
			<td><?= $product['goods_id'] ?></td>
			<td><?= $product['goods_name'] ?></td>
			<td><?= $product['stock_quantity'] ?></td>
			<td><?= $product['supplier_name'] ?></td>
		</tr>
	<?php endforeach; ?>
</table>


<h2>/api_v1_system_order/info</h2>
<?= Debug::dump($divisions['order']); ?>


<h2>/api_v1_system_credittype/info</h2>
<?= Debug::dump($divisions['credit']); ?>
