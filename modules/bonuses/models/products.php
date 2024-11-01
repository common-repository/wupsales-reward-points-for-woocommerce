<?php
class ProductsModelWsbp extends ModelWsbp {
	public $startRecalcPointsKey = 'wsbp_start_bonus';
	public $recalcProductsLockLimit = 20;

	public function __construct() {
		$this->_setTbl('products');
	}
	
	public function getProductPoints( $productId, $variationId = 0 ) {
		$productId = (int) $productId;
		$isVariation = !empty($variationId);
		$module = $this->getModule();
		if ($isVariation) {
			$variationId = (int) $variationId;
			$points = DbWsbp::get('SELECT id, points FROM `@__products` WHERE parent=' . $productId . ' AND id=' . $variationId, 'row');
		} else {
			$points = DbWsbp::get('SELECT id, points FROM `@__products` WHERE id=' . $productId, 'row');
		}
		if ($points) {
			return $module->getCurrencyPrice($points['points']);
		}
		if (!$isVariation) {
			$points = DbWsbp::get('SELECT min(IFNULL(points,0)) as min_p, max(IFNULL(points,0)) as max_p FROM `@__products` WHERE parent=' . $productId, 'row');
			if ($points) {
				return $module->getCurrencyPrice($points['min_p']) . ( $points['min_p'] != $points['max_p'] ? ' - ' . $module->getCurrencyPrice($points['max_p']) : '' );
			}
		}
		return false;
	}
	
	public function recalcProductsPoints( $productId = 0, $params = false ) {
		$result = $this->doRecalcProductsPoints($productId, $params);
		if (!$result) {
			FrameWsbp::_()->saveDebugLogging();
		}
		return $result;
	}
		
	public function getProductChildsWhere( $productId ) {
		$product = wc_get_product($productId);
		if (!$product) {
			FrameWsbp::_()->pushError('Product not found');
			return false;
		}
		$ids = $product->get_type() == 'variable' ? $product->get_children() : array();
		$ids[] = $productId;
		return ( count($ids) > 1 ? ' IN (' . implode(',', $ids) . ')' : '=' . $productId );
	}
	
	public function doRecalcProductsPoints( $productId, $params ) {
		if (!empty($productId) && !is_numeric($productId)) {
			return false;
		}
		$isAllProducts = empty($productId);

		$fullRecalc = $isAllProducts;
		$startRecalc = get_option($this->startRecalcPointsKey);

		if ($fullRecalc && !empty($startRecalc)) {
			if (microtime(true) - $startRecalc <= $this->recalcProductsLockLimit * 60) {
				FrameWsbp::_()->pushError('Wait. The products reward calculation is already running ...');
				return false;
			}
		}
		if ($fullRecalc) {
			update_option($this->startRecalcPointsKey, UtilsWsbp::getTimestamp());
		}
		$productList = ( $isAllProducts ? '' : $this->getProductChildsWhere($productId) );
		if (false === $productList) {
			return false;
		}
		
		$recalcPrPoints = UtilsWsbp::getArrayValue($params, 'recalcPrPoints', true);
		//$productsModel = $this-getModule()->getModel('products');
		
		$isPro = FrameWsbp::_()->isPro();

		if ($recalcPrPoints) {
			$productMetaKey = $this->getModule()->getModel('bonuses')->productMetaKey;
			
			$clear = 'DELETE FROM `@__products`' . ( $isAllProducts ? '' : ' WHERE id' . $productList );
			if (!DbWsbp::query($clear)) {
				FrameWsbp::_()->pushError('Error query: ' . $clear);
				FrameWsbp::_()->pushError(DbWsbp::getError());
				return false;
			}
			$insert = 'INSERT IGNORE INTO `@__products` (id, point, parent, price)' .
				' SELECT p.id, IFNULL(m.meta_value,mp.meta_value), p.post_parent, IFNULL(r.meta_value, 0)+0' .
				' FROM `#__posts` as p' .
				" LEFT JOIN (SELECT DISTINCT post_parent FROM `#__posts` pp WHERE pp.post_parent>0 AND pp.post_type='product_variation') as v ON (v.post_parent=p.ID)" .
				" LEFT JOIN `#__postmeta` as m ON (m.post_id=p.id AND m.meta_key='" . $productMetaKey . "')" .
				" LEFT JOIN `#__postmeta` as mp ON (mp.post_id=p.post_parent AND mp.meta_key='" . $productMetaKey . "')" .
				" LEFT JOIN `#__postmeta` as r ON (r.post_id=p.id AND r.meta_key='_price')" .
				" WHERE p.post_type IN ('product', 'product_variation')" .
				" AND (p.post_type='product_variation' OR v.post_parent IS NULL)" .
				( $isAllProducts ? '' : ' AND p.id' . $productList );
			if (!DbWsbp::query($insert)) {
				FrameWsbp::_()->pushError('Error query: ' . $insert);
				FrameWsbp::_()->pushError(DbWsbp::getError());
				return false;
			}
			$mainOptions = FrameWsbp::_()->getModule('bonuses')->getMainOptions();
			$decimals = 2;
			if (UtilsWsbp::getArrayValue($mainOptions, 'round_percent_point', false, 1)) {
				$decimals = UtilsWsbp::getArrayValue($mainOptions, 'round_percent_decimals', 2, 1, false, true);
			}
			$update = 'UPDATE `@__products`' .
				" SET pr_points=IF(ISNULL(point), NULL, IF(point LIKE '%\%%', ROUND((REPLACE(point,'%','')+0)*price/100," . $decimals . '), point+0)),' .
				' points=pr_points, calculated=NOW()' .
				( $isAllProducts ? '' : ' WHERE id' . $productList );
			if (!DbWsbp::query($update)) {
				FrameWsbp::_()->pushError('Error query: ' . $update);
				FrameWsbp::_()->pushError(DbWsbp::getError());
				return false;
			}
		}
		$result = true;
		if ($isPro) {
			$params['productId'] = $productId;
			$params['productList'] = $productList;
			$result = DispatcherWsbp::applyFilters('recalcProductsPoints', $result, $params);
		}
		if ($result && $fullRecalc) {
			update_option($this->startRecalcPointsKey, '');
		}
		return $result;
		
	}
}
