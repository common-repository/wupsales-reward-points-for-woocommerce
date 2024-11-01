<?php
	$options = FrameWsbp::_()->getModule('bonuses')->getMainOptions();
	$homeUrl = home_url();
	$siteName = get_bloginfo('name');
	//$siteLink = '<a href="' . esc_url(home_url()) . '">' . esc_html(get_bloginfo('name')) . '</a>';
	$currency = get_option('woocommerce_currency');
	$symbols = get_woocommerce_currency_symbols();
	$currencySymbol = ( isset($symbols[$currency]) ? $symbols[$currency] : $currency );
	$i = 1;
?>
<h2>Terms of use:</h2>
<p>By participating in the reward program, you automatically agree to the terms of participation in the reward system program.</p>

<p><b>1. Terms of participation in the program.</b><br />
1.<?php echo esc_html($i++); ?> Any natural person can become a member of the loyalty program - a consumer who is registered on the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> website.<br />
<?php if ($options['age_limit']) { ?>
1.<?php echo esc_html($i++); ?> Any natural person, who has turned <?php echo esc_html($options['min_age_user']); ?> years old, can become a participant in the program.<br />
<?php } ?>
1.<?php echo esc_html($i++); ?> By registering in the program, you thereby give the administration of the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> consent to the processing of your personal data listed below in order to conclude and execute contracts for the sale / provision of services, inform about goods, works, services and / or conduct surveys and research, participation in the loyalty program, including accounting for the accumulation and use of Rewards, to provide you with the most beneficial personalized offers from <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> and its partners, send you emails, and also allow you to transfer or entrust to others the processing of the following personal data.<br />
Consent is given to the processing of your following personal data: last name, first name, patronymic, gender, date of birth, mobile and/or home phone numbers, e-mail address, postal address, information about the history of purchases, including the names of the purchased goods/services and their value, accumulated Rewards, as well as information about interests based on data about your behavior on the Internet, in the networks of telecommunications and Internet operators, network and/or coalition (with the participation of partner companies) loyalty programs (hereinafter referred to as personal data). During the processing of your personal data, the following actions will be carried out with or without the use of automation tools: collection, recording, systematization, accumulation, storage, clarification (update, change), extraction, use, transfer (provision, access), depersonalization, blocking, deletion, destruction.<br />
1.<?php echo esc_html($i++); ?> From the moment of registration, the participant independently controls the change of his personal data in his personal account.<br />
1.<?php echo esc_html($i++); ?> In case of providing false (inaccurate) information about oneself, as well as in case of untimely change of outdated information, the participant assumes the risk of any negative consequences associated with the provision of incorrect information, up to the deduction of points or a ban on participation in the reward system.</p>

<?php 
	$cartOptions = FrameWsbp::_()->getModule('options')->getModel()->get('cart', '');
	$i = 0; 
?>
<p><b>2. Accrual of Rewards.</b><br />
2.1 The order of accrual or write-off of rewards is determined by the administration of the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a>.<br />
2.1.<?php echo esc_html(++$i); ?> Reward points can be awarded for the purchase of goods. The amount of rewards is determined by the administration of the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a>, the validity period of rewards accrued for the purchase of goods of <?php echo esc_html($options['expiry_date']); ?> days. Rewards are automatically credited to the Program Member's Reward Account after the online payment for the purchase, taking into account the receipt of reward points.<br />
<?php if (UtilsWsbp::getArrayValue($cartOptions, 'e_bonus', false)) { ?>
2.1.<?php echo esc_html(++$i); ?> Reward points may be awarded, depending on the amount of the check. Conditions for receiving rewards, depending on the check:<br />
<?php 
	foreach ($cartOptions as $c => $cart) {
		if (is_numeric($c)) {
			$minCart = UtilsWsbp::getArrayValue($cart, 'min', 0, 1);
			$maxCart = UtilsWsbp::getArrayValue($cart, 'max', false, 1);
			$bonus = UtilsWsbp::getArrayValue($cart, 'bonus', 0, 1);
			$isPercent = UtilsWsbp::getArrayValue($cart, 'unit') == '%';
			?>
If the paid cart amount is in the range <?php echo esc_html(( $maxCart ? '' : '>=' ) . $minCart . $currencySymbol . ( $maxCart ? ' - ' . $maxCart . $currencySymbol : '' )); ?> will be credited to the participant's account <?php echo esc_html($bonus . ( $isPercent ? '%' : ' points' )); ?> , excluding the amount paid by rewards from the total amount.<br />
<?php } ?>
<?php } ?>
<?php } ?>
2.1.<?php echo esc_html(++$i); ?> Rewards can be accrued by the decision of the administration, in the form of promotions and promotional campaigns.<br />
2.1.<?php echo esc_html($i); ?>.1 In the promotion held within the framework of the program, both all participants and their separate categories (target consumer focus groups), determined by the terms of a particular promotion, can participate.<br />
2.1.<?php echo esc_html($i); ?>.2 the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> independently determines: the terms and conditions of the promotion; categories of participants for which the promotion is carried out.<br />
2.1.<?php echo esc_html($i); ?>.3 Burnt promotional rewards are not restored.<br />
2.2 <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a>, at its discretion, may establish other grounds (cases) for accrual and / or non-accrual of Rewards, including for certain categories of participants.<br />
2.3 When crossing with other promotions with additional accrual of Rewards, they are accrued for each position of the check for only one, the most profitable promotion. Accruals on the amount of the check take into account only positions for which there were no other promotional accruals.<br />
2.4 Rewards cannot be exchanged for cash.<br />
2.5 The Participant, among other things, may not perform any of the following actions: give, sell or otherwise alienate Rewards, or the right to receive them, to other participants or other third parties; transfer Rewards or the rights to receive them as a pledge or otherwise impose encumbrances on them and / or rights to receive them.<br />
2.6 <a href="#" class="wsbp-widget-wrapper wsbp-inline" style="display: inline;">Balance and details</a> - when clicking on this link, the user can get acquainted with information about the number of Rewards on the account and the details of accruing and writing off reward points.</p>

<p><b>3. Validity of rewards</b><br />
3.1 The validity period of Rewards for purchases, on the account of the program member is <?php echo esc_html($options['expiry_date']); ?> days, and can be changed at any time by decision of the administration <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a>.<br />
<?php if (UtilsWsbp::getArrayValue($options, 'logic_expiry', 0, 1) == 1) { ?>
3.1.1 Each reward point has its own separate expiration date and is not renewed upon making a new purchase. At the end of the expiration date of points, reward points burn out.<br />
<?php } else { ?>
3.1.1 If during this period the member makes a new purchase on the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a>, then after the accrual of Rewards for this purchase, the validity period of all the member's available rewards in the available discount, with the exception of Rewards accrued on promotions, is again <?php echo esc_html($options['expiry_date']); ?> days (counting from day of the last purchase on the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a>). If, within <?php echo esc_html($options['expiry_date']); ?> days from the moment of receiving the Rewards accrued for the last purchase, the participant has not made any new purchase with the accrual of Rewards, then the Rewards available on his account in the available discount will expire. Burnt Rewards are non-refundable.<br />
<?php } ?>
3.2 The validity period of Rewards accrued as part of a promotion is determined by the terms of the relevant promotion, which is set by the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> at its sole discretion.<br />
3.3 The current state of the Rewards on the Reward account is contained in the participant's personal account on the <a href="#" class="wsbp-widget-wrapper wsbp-inline" style="display: inline;">Balance and details</a>. In order to avoid claims, <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> recommends that Members keep track of their Reward Points balance, as well as the date of expiration (burnout) of Rewards not activated in the discount, on their own.</p>

<?php $i = 1; ?>
<p><b>4. Payment with rewards</b><br />
4.1 When paying for a purchase with Rewards, the following number of reward points can be used:<br />
<?php if (UtilsWsbp::getArrayValue($options, 'e_max_points_cart', 0, 1) == 1) { ?>
4.1.<?php echo esc_html($i++); ?> The maximum amount of points for one cart is <?php echo esc_html(UtilsWsbp::getArrayValue($options, 'max_points_cart', 0, 1) . $currencySymbol); ?>.<br />
<?php } ?>
4.1.<?php echo esc_html($i++); ?> Rewards can be paid for an amount not exceeding <?php echo esc_html(UtilsWsbp::getArrayValue($options, 'e_max_percent_cart', 0, 1) == 1 ? UtilsWsbp::getArrayValue($options, 'max_percent_cart', 0, 1) : 100); ?>% of the cart.<br />
<?php if (UtilsWsbp::getArrayValue($options, 'e_min_cart', 0, 1) == 1) { ?>
4.1.<?php echo esc_html($i++); ?> To purchase with rewards, the minimum value of the cart must be equal to or greater than <?php echo esc_html(UtilsWsbp::getArrayValue($options, 'min_cart', 0, 1) . $currencySymbol); ?>.<br />
<?php } ?>
4.1.<?php echo esc_html($i++); ?> At the same time, goods with discounts are<?php echo esc_html(UtilsWsbp::getArrayValue($options, 'exclude_sales', 0, 1) == 1 ? '' : ' not'); ?> excluded from the total amount of the cart for calculating the allowable write-off with rewards by decision of the administration.<br />
4.2 Rewards are spent in chronological order: the ones with the earliest burn date are used first.<br />
4.3 The terms of payment with rewards can be changed at any time, by the decision of the administration of the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a>.</p>

<p><b>5. Refund of products</b> in which reward points took part.<br />
5.1 In the event of a return of goods partially or fully paid for with Rewards, they will be returned to the Member's Reward Account at the time of the return. In the event that the expiration date of the Reward Points at the time of the return has expired, the rewards will not be returned.<br />
5.2 If a participant pays for 2 or more goods with Rewards and then returns one of them, then the Rewards spent on the returned goods are returned to the participant's Reward account. Rewards credited to the participant's account will be available for discount immediately after they are returned to the account.<br />
5.3 If, when returning the goods, at the time of the refund, the client does not have enough Rewards on the Reward account to write off the amount previously accrued for the purchase and spent by the client, then <?php echo esc_html(UtilsWsbp::getArrayValue($options, 'refund_type', 0, 1) == 2 ? 'rewards will not be debited' : 'the maximum possible number of rewards will be written off'); ?>.</p> 

<?php 
	$email = get_option('admin_email');
?>
<p><b>6. Additional Rules</b><br />
6.1 The <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> may send the member information about the reward account, accumulated Rewards, changes in the rules of the loyalty program by sending messages to the <?php echo esc_html($email); ?>.<br />
6.2 In order to inform about news and promotions, the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> company may send him advertising and / or marketing information about the goods, works, services of the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> and its partners, about news and promotions by sending messages to the <?php echo esc_html($email); ?>.<br />
6.3 If the participant wants to refuse participation in the program, it is necessary to go to the personal account of the reward program <a href="#" class="wsbp-widget-wrapper wsbp-inline" style="display: inline;">Balance and details</a> and click "refuse to participate", and then confirm this intention.<br />
6.4.1 If the participant wants to resume participation in the reward program, it is necessary to go to the personal account of the reward program <a href="#" class="wsbp-widget-wrapper wsbp-inline" style="display: inline;">Balance and details</a> and click "Resume participation", and then confirm this intention.<br />
6.4.2 A participant, who has renewed participation, retains all points with an unexpired expiration date.<br />
6.5 The participant may be blocked in the reward program by decision of the administration. If you do not agree with this decision, please contact the administration. In this case, only the website administration can restore participation in the reward program.<br />
6.6 The validity period of the program is not limited. <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> has the right to terminate the program at any time.<br />
6.7 <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> has the right to unilaterally change the terms of the program at any time. The terms of the program with changes are published on the program website at the <a href="<?php echo esc_url($this->getModule()->getPageRulesUrl()); ?>" target="_blank">Terms of use</a>.<br />
6.8 The document confirming the company's obligation to accrue Rewards to the participant's account is an electronic check (or other document confirming the purchase within the program). All claims on the fact of accrual of Rewards are considered by the company only upon presentation of the documents specified above.<br />
6.9 Rewards can be written off at the initiative of the company without prior notice to the program participant if they were credited to the participant's account erroneously, as a result of the actions of the participant or another person containing elements of bad faith, or for other reasons determined by the decision of the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> administration.<br />
6.10 The Company has the right to terminate participation in the program of any participant and block / cancel the Reward account without notice in cases where the participant: - does not comply with these conditions, as well as the conditions of other promotions of the company; — has made or intends to make a sale or purchase of rewards to third parties; — allowed or intends to allow a third party to place an order through his personal account on the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> or in a mobile application; - has committed or intends to commit actions that have significant signs of fraud, deceit or other manipulations that have caused or may cause material or moral damage and other negative consequences; - abuses any rights granted to the participant under the program; – did not fill in the required fields of the questionnaire/application-questionnaire or filled it out incorrectly which prevents from confirming participant's identity; - provides information (misinformation) that is misleading or does not correspond to reality; — in accordance with the requirements of the current legislation; — if the facts indicate that the participant’s purchases are made for business purposes, that is, for their subsequent sale / resale, or on behalf of / at the expense of a legal entity to carry out the activities of a legal entity, or on behalf of / at the expense of an individual received from a group of individuals, for the acquisition of equipment for general use and / or donation.</p>

<p><a href="https://woobewoo.com/plugins/reward-points-for-woocommerce/" target="_blank"><?php echo esc_html(WSBP_WP_PLUGIN_NAME); ?></a><br />
The organization of the Reward system is provided by the <a href="https://woobewoo.com/plugins/reward-points-for-woocommerce/" target="_blank">Wupsales</a>. <a href="https://woobewoo.com/plugins/reward-points-for-woocommerce/" target="_blank">Wupsales</a> is not responsible for the methods of use and unfair use of the reward system service for fraudulent purposes or any purposes contrary to local law.<br />
All settings and functionality of the provided service are configured personally by the <a href="<?php echo esc_url($homeUrl); ?>"><?php echo esc_html($siteName); ?></a> administration.</p>
