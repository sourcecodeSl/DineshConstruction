<!doctype html>
<?php
include 'class/include.php';
include_once 'gatepass-sheet.php';

if (!isset($_SESSION)) {
    session_start();
}

$id = $_GET['id'] ?? '';

$GATEPASS = new Gatepass($id);

if (!$GATEPASS->id) {
    die('Gatepass not found');
}

$EQUIPMENT_RENT = new EquipmentRent($GATEPASS->invoice_id);
$CUSTOMER = new CustomerMaster($EQUIPMENT_RENT->customer_id);
$GATEPASS_ITEMS = $GATEPASS->getItems();

?>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Gate Pass - <?php echo htmlspecialchars($GATEPASS->gatepass_code); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'main-css.php' ?>
    <link href="https://unicons.iconscout.com/release/v4.0.8/css/line.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body, html {
                width: 100%;
                margin: 0;
                padding: 0;
                background-color: #fff !important;
            }

            #invoice-content, .card {
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
            }

            @page {
                size: A5;
                margin: 5mm;
            }
        }

        #invoice-content {
            background-color: #fffbef; /* Slight yellowish tint for paper look on screen */
            width: 148mm;
            margin: auto;
            min-height: 210mm;
            padding: 10mm;
            font-family: 'Inter', sans-serif;
            color: #1a1a1a;
        }

        @media print {
            #invoice-content {
                background-color: #fff !important;
                padding: 10mm;
                min-height: auto;
            }
        }

    </style>
    <?php renderGatepassStyles(); ?>

</head>

<body data-layout="horizontal" data-topbar="colored">

<div class="container mt-4 no-print">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Gate Pass Preview</h4>
        <div class="d-flex gap-2">
             <a href="gatepass.php?rent_id=<?php echo $GATEPASS->invoice_id; ?>" class="btn btn-secondary">
                <i class="uil uil-arrow-left"></i> Back
            </a>
            <button onclick="window.print()" class="btn btn-success">
                <i class="uil uil-print"></i> Print
            </button>
            <button onclick="downloadPDF()" class="btn btn-primary">
                <i class="uil uil-file-download"></i> PDF
            </button>
        </div>
    </div>
</div>

<div id="invoice-content">
    <?php renderGatepassSheet($GATEPASS, $EQUIPMENT_RENT, $CUSTOMER); ?>
    
    <!-- Footer Internal Note (Bill Ref) -->
    <div style="margin-top: 20px; font-size: 10px; color: #888; text-align: right;" class="no-print">
        Bill Ref: <?php echo htmlspecialchars($EQUIPMENT_RENT->bill_number); ?> (Internal Ref)
    </div>
</div>

<!-- JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadPDF() {
        const element = document.getElementById('invoice-content');
        const opt = {
            margin: [5, 5, 5, 5],
            filename: 'Gatepass_<?php echo $GATEPASS->gatepass_code; ?>.pdf',
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 2,
                useCORS: true
            },
            jsPDF: {
                unit: 'mm',
                format: 'a5',
                orientation: 'portrait'
            }
        };
        html2pdf().set(opt).from(element).save();
    }

    // Trigger print on Enter
    document.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            window.print();
        }
    });
</script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
