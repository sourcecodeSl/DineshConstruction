<?php
/**
 * Shared gate pass layout.
 * Used by gatepass-print.php (stand-alone) and rent-invoice.php (page 2 of the bill).
 * All styles are scoped under .gp-sheet so they do not clash with Bootstrap or the invoice.
 */

if (!function_exists('renderGatepassStyles')) {
    function renderGatepassStyles()
    {
        ?>
        <style>
            .gp-sheet {
                font-family: 'Inter', sans-serif;
                color: #1a1a1a;
            }
            .gp-sheet .gp-header-title { text-align: center; font-weight: bold; font-size: 18px; margin-bottom: 5px; color: #000; }
            .gp-sheet .gp-header-subtitle { text-align: center; font-size: 12px; font-weight: bold; color: #333; margin-bottom: 15px; }
            .gp-sheet .gp-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
            .gp-sheet .gp-number { font-size: 18px; font-weight: bold; color: #444; }
            .gp-sheet .gp-date { font-size: 13px; font-weight: bold; }
            .gp-sheet .gp-date span { border-bottom: 2px dotted #888; min-width: 100px; display: inline-block; text-align: center; }
            .gp-sheet .gp-main-title { text-align: center; font-weight: bold; font-size: 16px; text-decoration: underline; margin-bottom: 15px; }
            .gp-sheet .gp-box { border: 2px solid #555; border-radius: 12px; padding: 15px; position: relative; }
            .gp-sheet .gp-row { margin-bottom: 5px; display: flex; align-items: baseline; }
            .gp-sheet .gp-label { font-size: 12px; font-weight: 500; white-space: nowrap; margin-right: 10px; color: #000; }
            .gp-sheet .gp-value { border-bottom: 2px dotted #888; flex-grow: 1; font-size: 13px; font-weight: bold; padding-left: 10px; color: #003366; min-height: 15px; }
            .gp-sheet .gp-items { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 10px; }
            .gp-sheet .gp-items th, .gp-sheet .gp-items td { border: 1px solid #333 !important; padding: 6px 10px; text-align: left; font-size: 12px; }
            .gp-sheet .gp-items th { background-color: #f2f2f2; font-weight: bold; }
            .gp-sheet .gp-items td { font-weight: bold; color: #003366; }
            .gp-sheet .gp-sign { margin-top: 10px; }
            .gp-sheet .gp-sign-title { font-weight: bold; font-size: 12px; margin-bottom: 10px; text-decoration: underline; }
            .gp-sheet .gp-auth { margin-top: 15px; font-size: 12px; line-height: 1.6; color: #000; }
            .gp-sheet .gp-manager { text-align: right; margin-top: 25px; padding-right: 40px; }
            .gp-sheet .gp-manager-line { display: inline-block; width: 200px; border-bottom: 2px dotted #888; margin-bottom: 5px; }
            .gp-sheet .gp-manager-label { display: block; width: 200px; text-align: center; font-weight: bold; font-size: 12px; margin-left: auto; }
            @media print {
                .gp-sheet { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
        </style>
        <?php
    }
}

if (!function_exists('renderGatepassSheet')) {
    function renderGatepassSheet($GATEPASS, $EQUIPMENT_RENT, $CUSTOMER)
    {
        $items = $GATEPASS->getItems();
        ?>
        <div class="gp-sheet">
            <div class="gp-header-title">පී. එස්. එකමුතු එන්ටර්ප්‍රයිසිස්</div>
            <div class="gp-header-subtitle">
                අංක 50, හිල් වීදිය, දෙහිවල. දුරකථන : 4201659, 077 6358281
            </div>

            <div class="gp-meta">
                <div class="gp-number"><?php echo htmlspecialchars($GATEPASS->gatepass_code); ?></div>
                <div class="gp-date">බිල්පත් අංකය (Bill No) : <span><?php echo htmlspecialchars($EQUIPMENT_RENT->bill_number); ?></span></div>
                <div class="gp-date">දිනය : <span><?php echo date('d/m/Y', strtotime($GATEPASS->gatepass_date)); ?></span></div>
            </div>

            <div class="gp-main-title">ගබඩා සංවීතයෙන් උපකරණ නිකුත් කිරීමේ අවසර පත්‍රය</div>

            <div class="gp-box">
                <div class="gp-row">
                    <div class="gp-label">නම :</div>
                    <div class="gp-value">
                        <?php
                        echo htmlspecialchars($GATEPASS->name);
                        if (!empty($CUSTOMER->mobile_number)) {
                            echo " (" . htmlspecialchars($CUSTOMER->mobile_number) . ")";
                        }
                        ?>
                    </div>
                </div>
                <div class="gp-row">
                    <div class="gp-label">ලිපිනය :</div>
                    <div class="gp-value"><?php echo htmlspecialchars($GATEPASS->address); ?></div>
                </div>
                <div class="gp-row">
                    <div class="gp-label">හැඳුනුම්පත් අංකය :</div>
                    <div class="gp-value"><?php echo htmlspecialchars($GATEPASS->id_number); ?></div>
                </div>

                <table class="gp-items">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>විස්තරය</th>
                            <th style="width: 100px; text-align: center;">ප්‍රමාණය</th>
                            <th>කරුණු</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $index + 1; ?></td>
                                <td>
                                    <?php
                                    echo htmlspecialchars($item['equipment_name']);
                                    if ($item['sub_equipment_code']) {
                                        echo " (" . htmlspecialchars($item['sub_equipment_code']) . ")";
                                    }
                                    ?>
                                </td>
                                <td style="text-align: center;"><?php echo $item['quantity'] + 0; ?></td>
                                <td><?php echo htmlspecialchars($item['remarks'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">උපකරණ ඇතුළත් කර නොමැත (No items listed)</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="gp-sign">
                    <div class="gp-sign-title">නිකුත් කරන්නාගේ</div>
                    <div class="gp-row">
                        <div class="gp-label">නම :</div>
                        <div class="gp-value"><?php echo htmlspecialchars($GATEPASS->issued_by); ?></div>
                    </div>
                    <div class="gp-row">
                        <div class="gp-label">අත්සන :</div>
                        <div class="gp-value" style="color: transparent;">____________________</div>
                    </div>
                </div>

                <div class="gp-auth">
                    ඉහත සඳහන් යන්ත්‍රය / උපකරණ නිකුත් කිරීමට අවසර දෙමි.
                </div>

                <div class="gp-manager">
                    <span class="gp-manager-line"></span>
                    <span class="gp-manager-label">කළමනාකරු</span>
                </div>
            </div>
        </div>
        <?php
    }
}
