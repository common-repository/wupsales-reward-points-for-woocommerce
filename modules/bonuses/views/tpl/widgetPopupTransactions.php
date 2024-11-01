<?php 
$trans = $this->trans;
$transModel = $this->getModule()->getModel('transactions');
$detailsModel = $this->getModule()->getModel('details');
$module = $this->getModule();
$users = array();

if (empty($trans)) {
	echo '<tr class="wsbp-empty-trans"><td colspan="4">' . esc_html__('You have no transactions for now.', 'wupsales-reward-points') . '</td></tr>';
} else {
	foreach ($this->trans as $i => $tran) { 
		$sub = array();
		?>
		<tr class="wsbp-widget-tran">
			<td class="wsbp-tran-date"><?php echo esc_html($tran['created']); ?></td>
			<td class="wsbp-tran-status"><?php echo esc_html($transModel->getFieldLists('status', $tran['status'])); ?></td>
			<td class="wsbp-tran-operation">
				<?php 
				switch ($tran['tr_type']) {
					case 0:
					case 1:
						echo esc_html($tran['reason']);
						if (!$this->is_front) {
							$author = $tran['author'];
							if (!isset($users[$author])) {
								$u = get_userdata($author);
								$users[$author] = $u ? $u->display_name : $author;
							}
							echo '<div class="wsbp-det-author">' . esc_html__('Author', 'wupsales-reward-points') . ': ' . esc_html($users[$author]) . '</div>';
						}
						break;
					case 2:
						esc_html_e('Purchase', 'wupsales-reward-points');
						//$order = wc_get_order($tran['op_id']);
						$details = $detailsModel->getTransactionDetails($tran['id']);
						echo '<div class="wsbp-det-order">' . esc_html__('Order', 'wupsales-reward-points') . '#' . esc_html($tran['op_id']) . '</div>';
						if ($details) {
							foreach ($details as $det) {
								echo '<div class="wsbp-det-source">';
								if (1 == $det['source']) {
									$_product = wc_get_product($det['source_id']);
									echo 'x' . esc_html($det['pur_cnt'] . ' ' . ( $_product ? $_product->get_name() : '???' ));
								} else {
									echo esc_html($detailsModel->getFieldLists('source', $det['source']));
								}
								echo '</div>';
								$sub[] = $det['points'];
							}
						}
						break;
					case 3:
						esc_html_e('Purchase', 'wupsales-reward-points');
						break;
					default:
						break;
				}
				?>
			</td>
			<td class="wsbp-tran-points">
				<?php 
				echo esc_html($module->getCurrencyPrice($tran['points']));
				if (!empty($sub)) {
					echo '<div class="wsbp-det-empty">&nbsp;</div>';
					foreach ($sub as $ss) {
						echo '<div class="wsbp-det-points">' . esc_html($module->getCurrencyPrice($ss)) . '</div>';
					}
				}
				?>
			</td>
		</tr>
	<?php 
	}
} 
?>
