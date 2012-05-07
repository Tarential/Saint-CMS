<?php
$user = Saint::getCurrentUser();
$page = Saint::getCurrentPage();
$args = $page->getArgs();
$matches = array();
if (isset($args['ppid'])) {
	$matches[] = array('paypalid',$args['ppid']);
}
if (isset($args['ppuser'])) {
	$matches[] = array('paypaluser',$args['ppuser']);
}
if (isset($args['amount'])) {
	$matches[] = array('amount',$args['amount']);
}
?>
<?php if ($user->hasPermissionTo("view-transactions")): ?>
<?php $transactions = Saint_Model_Transaction::getTransactions( array( "matches" => $matches ) ); ?>
<h2>Sales Report</h2>
<table>
	<tr>
		<th class="date">Date</th>
		<th class="id">ID</th>
		<th class="paypal">PayPal Account</th>
		<th class="amount">Amount</th>
	</tr>
<?php foreach ($transactions as $tid): ?>
<?php $ct = new Saint_Model_Transaction(); $ct->load($tid); ?>
	<tr>
		<td><?php echo date("Y-m-d",strtotime($ct->getDate())); ?></td>
		<td><?php echo $ct->getPayPalId(); ?></td>
		<td><?php echo $ct->getPayPalUser(); ?></td>
		<td><?php echo $ct->getAmount(); ?></td>
	</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
Permission Denied
<?php endif; ?>
