<!DOCTYPE html>
<html>
<head>
	<title>Purchase Order</title>
	<style type="text/css">
		body {
			font-family: 'Calibri',sans-serif;
			font-size: 12px;
		}

		.align-right {
			text-align: right;
		}

		.align-left {
			text-align: left;
		}

		.align-center {
			text-align: center;
		}

		.report-header {
			font-weight: bolder;
		}
	</style>
</head>
<body>
	<div class="">
		<strong>P.O. # :</strong> <?php echo $purchase_info->po_no; ?></td> <br>
		<strong>Date : </strong><?php echo date_format(new DateTime($purchase_info->date_created),"m/d/Y"); ?>
	</div><br>
	<table width="100%" border="1" cellspacing="-1">
		<tr>
			<td style="padding: 6px;" width="50%" colspan="2"><strong>Supplier / Address</strong></td>
			<td style="padding: 6px;" width="50%"><strong>Deliver to</strong></td>
		</tr>
		<tr>
			<td style="padding: 6px;" width="50%" colspan="2"><?php echo $purchase_info->supplier_name; ?></td>
			<td style="padding: 6px;" width="50%"><?php echo $purchase_info->deliver_to_address; ?></td>
		</tr>
		<tr>
			<td style="padding: 6px;" width="25%"><strong>Terms :</strong></td>
			<td style="padding: 6px;" width="25%"><strong>Ref # :</strong></td>
			<td style="padding: 6px;" rowspan="2"></td>
		</tr>
		<tr>
			<td style="padding: 6px;" width="25%"><?php echo $purchase_info->terms; ?></td>
			<td style="padding: 6px;" width="25%"></td>
		</tr>
	</table>
	<br>
	<table width="100%" cellpadding="10" cellspacing="-1" border="1" style="text-align: center;">
		<tr>
			<td style="padding: 6px;"><strong>Description</strong></td>
			<td style="padding: 6px;"><strong>UM</strong></td>
			<td style="padding: 6px;"><strong>Qty</strong></td>
			<td style="padding: 6px;"><strong>Unit Price</strong></td>
			<td style="padding: 6px;"><strong>Amount</strong></td>
		</tr>
		<?php foreach($po_items as $item){ ?>
            <tr>
                <td width="50%" style="text-align: left;height: 10px;padding: 6px;"><?php echo $item->product_desc; ?></td>
                <td width="10%" style="text-align: center;height: 10px;padding: 6px;"><?php echo $item->unit_name; ?></td>
                <td width="10%" style="text-align: right;height: 10px;padding: 6px;"><?php echo number_format($item->po_qty,0); ?></td>
                <td width="15%" style="text-align: right;height: 10px;padding: 6px;"><?php echo number_format($item->po_price,2); ?></td>
                <td width="15%" style="text-align: right;height: 10px;padding: 6px;"><?php echo number_format($item->po_line_total,2); ?></td>
            </tr>
        <?php } ?>
        <tr>
        	<td style="padding: 6px;" align="left" colspan="2"><strong>Remarks</strong></td>
        	<td style="padding: 6px;" align="left" colspan="2"><strong>Total Before Tax</strong></td>
        	<td style="padding: 6px;" align="right"><strong><?php echo number_format($purchase_info->total_before_tax,2); ?></strong></td>
        </tr>
        <tr>
        	<td style="padding: 6px;" align="left" colspan="2" rowspan="2"><?php echo $purchase_info->remarks; ?></td>
        	<td style="padding: 6px;" align="left" colspan="2"><strong>Tax</strong></td>
        	<td style="padding: 6px;" align="right"><strong><?php echo number_format($purchase_info->total_tax_amount,2); ?></strong></td>
        </tr>
        <tr>
        	<td style="padding: 6px;" align="left" colspan="2"><h4><strong>Total After Tax</strong></h3></td>
        	<td style="padding: 6px;" align="right"><h4><strong><?php echo number_format($purchase_info->total_after_tax,2); ?></strong></h4></td>
        </tr>
	</table>
</body>
</html>