<?php
//header("Content-Type: text/html; charset=windows-1251");

// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

$filename = date('Y_m_d_H_i_s_').'export_orders.csv';

if($_POST['order']){
	header("Content-Disposition: attachment; filename=".$filename);

	$tableName = 'modx_manager_shopkeeper';

	//charset=cp1251 При необходимости
	try {
		$db = new PDO('mysql:host=localhost;dbname=unipressby_unipress2', 'unipress_new', 'n;(RL*B$yB?6');
	} 
	catch (PDOException $e) {
		echo $e->getMessage();
	}

	$product_list = array();
	$result = array();
	$sql = 'SELECT * FROM '.$tableName.' ORDER BY id ASC';
	$sth = $db->prepare($sql);
	$sth->execute(array());
	$result = $sth->fetchAll(PDO::FETCH_ASSOC);

	class Essence
	{
		public function __construct($arr)
		{
			return $this->fillFields($arr);
		}

		public function fillFields($arr)
		{
			foreach ($arr as $key => $value) {
				$this->$key = $value;
			}
		}

		public function debug($arr)
		{
			echo '<pre>';
			var_dump($arr);
			echo '</pre>';
		}

	}

	class Product extends Essence
	{
		
		public function fillFields($arr)
		{
			foreach ($arr as $key => $value) {
				if($key == '0'){
					$this->id = $value;
				}
				else if($key == '1'){
					$this->qty = $value;	
				}
				else if($key == '2'){
					$this->price = $value;	
				}
				else if($key == '3'){
					$this->title = $value;	
				}
				else if($key == 'tv'){
					//Получение tv товара. Например артикула
					$this->articul = $value['code'];	
				}
			}
		}

	}//Product

	class Order extends Essence
	{
		public function fillFields($arr)
		{
			foreach ($arr as $key => $value) {
				if($key == 'short_txt'){
					$short_txt = unserialize(preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $value));
					$this->name = $short_txt['name'];
				}
				else{
					$this->$key = $value;	
				}
			}
		}

		public static function cleanSymbol($var)
		{
			$var = str_replace('', '', $var);
			$var = str_replace('"', '', $var);
			$var = str_replace("'", '', $var);
			$var = str_replace("?", ' ', $var);
			$var = str_replace("/", ' ', $var);
			$var = str_replace("©", '', $var);
			$var = str_replace("®", '', $var);
			$var = str_replace("™", '', $var);
			$var = str_replace("°", '', $var);
			$var = str_replace("<", '', $var);
			$var = str_replace(">", '', $var);
			$var = str_replace("«", '', $var);
			$var = str_replace("»", '', $var);
			$var = str_replace("„", '', $var);
			$var = str_replace("”", '', $var);
			$var = str_replace("‚", '', $var);
			$var = str_replace("’", '', $var);
			$var = str_replace("‘", '', $var);
			$var = str_replace("—", '', $var);
			$var = str_replace("–", '', $var);
					
			return $var;
		}

	}//Order

	$out = fopen('php://output', 'w');

	$product_list[1][] = iconv("UTF-8", "windows-1251", 'Номер заказа');
	$product_list[1][] = iconv("UTF-8", "windows-1251", 'Номер позиции в заказе');
	$product_list[1][] = iconv("UTF-8", "windows-1251", 'id товара');
	$product_list[1][] = iconv("UTF-8", "windows-1251", 'Артикул товара');
	$product_list[1][] = iconv("UTF-8", "windows-1251", 'Название товара');
	$product_list[1][] = iconv("UTF-8", "windows-1251", 'Цена товара');
	$product_list[1][] = iconv("UTF-8", "windows-1251", 'Количество товара');
	$product_list[1][] = iconv("UTF-8", "windows-1251", 'Стоимость товара');
	$product_list[1][] = iconv("UTF-8", "windows-1251", 'Дата заказа');
	$product_list[1][] = iconv("UTF-8", "windows-1251", 'Имя покупателя');
	$product_list[1][] = iconv("UTF-8", "windows-1251", 'Номер телефона покупателя');
	$product_list[1][] = iconv("UTF-8", "windows-1251", 'Email покупателя');
	fputcsv($out, $product_list[1], ';');


	$i = 2;
	foreach ($result as $item) {

		$order = new Order($item);
			
		$products = unserialize(preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $item['content']));
				
		$j = 1;
		foreach ($products as $product) {

			$objProduct = new Product($product);	

			//$objProduct->debug($objProduct);

			$product_list[$i][] = $order->id;
			$product_list[$i][] = $j;
			$product_list[$i][] = $objProduct->id;
			
			$orderArticul = $objProduct->articul;
			$orderArticul = Order::cleanSymbol($orderArticul);
			$product_list[$i][] = $orderArticul;
			//$product_list[$i][] = $objProduct->articul;

			$orderTitle = $objProduct->title;
			$orderTitle = Order::cleanSymbol($orderTitle);
			$product_list[$i][] = $orderTitle;
			//$product_list[$i][] = $objProduct->title;

			$product_list[$i][] = str_replace('.', ',', $objProduct->price);
			$product_list[$i][] = $objProduct->qty;
			$product_list[$i][] = str_replace('.', ',', $objProduct->price * $objProduct->qty);
			$product_list[$i][] = $order->date;
			
			$orderName = $order->name;
			$orderName = Order::cleanSymbol($orderName);
			$product_list[$i][] = $orderName;

			$product_list[$i][] = $order->phone . '';
			$product_list[$i][] = $order->email;


			//$objProduct->debug($product_list);

			fputcsv($out, $product_list[$i], ';');
				
			$i++;
			$j++;
		}
		
	}

fclose($out);
	
}//$_POST
else{ 
	echo '<form action="#" method="POST">
	<input class="btn btn-success" type="submit" name="order" value="Сгенерировать список заказов">
	</form>';
}
?>
