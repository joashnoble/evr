<!DOCTYPE html>
<html>
<head>
	<title>Customers Sales Summary Report</title>
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

        .data {
            border-bottom: 1px solid #404040;
        }

        .align-center {
            text-align: center;
        }

        .report-header {
            font-size: 22px;
        }

        hr {
            border-top: 1px solid #404040;
        }
        @media print{@page {size: landscape}}
    </style>
    <script type="text/javascript">
        (function(){
            window.print();
            setTimeout(function() {
                 //window.close();
             }, 500);
        })();
    </script>    
</head>
<body>
   <table width="100%">
        <tr>
            <td width="10%"><img src="<?php echo base_url($company_info->logo_path); ?>" style="height: 90px; width: 120px; text-align: left;"></td>
            <td width="90%" class="align-center">
                <span class="report-header"><strong><?php echo $company_info->company_name; ?></strong></span><br>
                <span><?php echo $company_info->company_address; ?></span><br>
                <span><?php echo $company_info->landline.'/'.$company_info->mobile_no; ?></span>
            </td>
        </tr>
    </table><hr>
    <div>
        <span ><strong>CUSTOMER SALES REPORT</strong></span><br>
        <span>Period <?php echo $_GET['startDate']; ?> to <?php echo $_GET['endDate']; ?></span>
    </div>
    
    <table width="95%" style="margin-left: 5%; text-align: right;">
        <thead>
            <tr style="text-transform: uppercase;">
                <td width="25%" style="text-align: left;"><strong>Customer</strong></td>
                <td width="50%" style="text-align: left;"><strong>Address</strong></td>
                <td width="25%"><strong>Invoice Amount</strong></td>
            </tr><hr>
        </thead>
        <tbody>
            <?php $sum=0; 
                foreach($sales_summary as $summary) { ?>
                        <tr>
                            <td style="text-align: left;"><?php echo $summary->customer_name; ?></td>
                            <td style="text-align: left;"><?php echo $summary->address; ?></td>
                            <td><?php echo number_format($summary->total_after_tax,4); ?></td>
                        </tr>
                    <?php
                    $sum+=$summary->total_after_tax; 
                }
            ?>
            <tr>
                <td></td>
                <td></td>
                <td><h2><?php echo number_format($sum,4); ?></h2></td>
            </tr>
        </tbody>
    </table>
</body>
</html>