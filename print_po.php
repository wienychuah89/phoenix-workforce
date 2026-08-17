<?php
include 'cnopen.php';
$pono = isset($_GET['pono']) ? trim($_GET['pono']) : '';

if (empty($pono)) {
    die('Invalid Purchase Order Number.');
}

// 1. Fetch Header Details
$stmtHdr = $pdo->prepare("SELECT pono, posuppid, suppname, suppadd, supptel, podate, podateline, postts, pormk, pocurr,
IFNULL((SELECT ROUND(SUM(podprice * podqty), 2) FROM podet WHERE podno = pono AND podstts <> 'X'), 0) AS ttlpo,
IFNULL((SELECT COUNT(*) FROM podet WHERE podno=pono AND podstts<>'X'),0) AS ttldtl	
FROM pohdr LEFT JOIN supplier on suppid=posuppid WHERE pono=:pono ORDER by pono DESC");
$stmtHdr->execute(['pono' => $pono]);
$header = $stmtHdr->fetch(PDO::FETCH_ASSOC);

/* echo json_encode([
	'success' => true,
	'header' => $stmtHdr
]); */
	
if (!$header) {
    die('Purchase Order not found.');
}

$stmtDet = $pdo->prepare("SELECT podno, poditm,prodname, podqty, poduom, podprice, CASE WHEN podprice = 0 THEN 0 ELSE podqty * podprice END AS amt FROM podet LEFT JOIN product ON prodid=poditm  WHERE podno=:pono ORDER BY podid ASC;");
$stmtDet->execute(['pono' => $pono]);
$items = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

?>	
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PO Report - <?= htmlspecialchars($header['pono'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>-->
    <style>
        /* Screen preview styling */
        body {
            background-color: #525659;
            margin: 0;
            padding: 24px 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #212529;
        }

        .a4-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 20px auto;
            background: #ffffff;
            padding: 15mm;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.35);
            box-sizing: border-box;
        }

        .action-bar {
            width: 210mm;
            margin: 0 auto 12px auto;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        /* Core Table Layout */
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table td, 
        .report-table th {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
        }

        .header-cell {
            border: none !important;
            padding: 0 0 10px 0 !important;
            background: transparent !important;
            font-weight: normal;
        }

        .info-box {
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            font-size: 11px;
        }

        .po-footer-section {
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .notice-box {
            border: 1px solid #dee2e6;
            border-left: 3px solid #0d6efd;
            background-color: #fcfcfc;
            padding: 10px 14px;
            font-size: 11px;
            color: #555;
            border-radius: 2px;
        }

        .signature-box {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .sign-line {
            width: 42%;
            border-top: 1px solid #333;
            text-align: center;
            padding-top: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Essential Print Rules */
        @media print {
            html, body {
                background: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .a4-page {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                position: static !important;
            }
            @page {
                size: A4 portrait;
                margin: 12mm 12mm 15mm 12mm;
				/* Bottom right footer page counter */
				@bottom-right {
					content: "Page " counter(page) " / " counter(pages);
					font-size: 10px;
					color: #6c757d;
					font-family: Arial, sans-serif;
				}
            }
            thead {
                display: table-header-group !important; /* Forces repeating the header */
            }
            tbody {
                display: table-row-group !important;
            }
            tr {
                page-break-inside: avoid !important;
            }
            tfoot {
                display: table-row-group !important; /* Prevents tfoot from repeating */
            }
            .po-footer-section {
                page-break-inside: avoid !important;
            }
			
        }
    </style>
</head>
<body>

    <!-- Screen Buttons (Hidden on Print) -->
    <div class="action-bar no-print">
        <button class="btn btn-primary btn-sm px-3" onclick="window.print()">
            <i class="bi bi-printer"></i> Print PO
        </button>
        <button class="btn btn-secondary btn-sm px-3" onclick="window.close()">Close</button>
    </div>

    <!-- Centered A4 Sheet -->
    <div class="a4-page">

        <table class="report-table">
            <thead>
                <!-- Row 1: Company Name and Document Title -->
                <tr>
                    <td colspan="3" class="header-cell" style="vertical-align: top; text-align: left;">
                        <h3 style="margin: 0; font-size: 18px; font-weight: bold; color: #0d6efd;">YOUR COMPANY NAME</h3>
                        <div style="font-size: 10px; color: #6c757d; line-height: 1.3; margin-top: 2px;">
                            123 Business Avenue, Suite 400<br>
                            Penang, Malaysia<br>
                            TEL: +60 4-123 4567 | EMAIL: PURCHASING@EXAMPLE.COM
                        </div>
                    </td>
                    <td colspan="2" class="header-cell" style="vertical-align: top; text-align: right;">
                        <div style="font-size: 20px; font-weight: 800; color: #212529;">PURCHASE ORDER</div>
                        <div style="font-size: 14px; font-weight: bold; color: #0d6efd;"><?= htmlspecialchars($header['pono'] ?? '') ?></div>
                    </td>
                </tr>

                <!-- Row 2: Header Divider Line -->
                <tr>
                    <td colspan="5" class="header-cell" style="border-bottom: 2px solid #dee2e6 !important; padding-bottom: 6px !important;"></td>
                </tr>

                <!-- Row 3: Vendor Details & PO Meta Info -->
                <tr>
                    <td colspan="3" class="header-cell" style="vertical-align: top; padding-top: 8px !important; padding-right: 8px !important;">
                        <div class="info-box">
                            <div style="font-weight: bold; color: #6c757d; font-size: 9px; text-transform: uppercase;">Vendor / Supplier:</div>
                            <div style="font-size: 13px; font-weight: bold; margin-top: 2px;"><?= htmlspecialchars($header['suppname'] ?? '-') ?></div>
                            
							<div style="margin-top: 2px;"><strong>Address:</strong> <?= htmlspecialchars($header['suppadd'] ?? '-') ?></div>
						    <div style="margin-top: 2px;"><strong>Tel.:</strong> <?= htmlspecialchars($header['supptel'] ?? '-') ?></div>
                            <?php if (!empty($header['pormk'])): ?>
                                <div style="color: #6c757d; margin-top: 2px;"><strong>Remark:</strong> <?= nl2br(htmlspecialchars($header['pormk'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td colspan="2" class="header-cell" style="vertical-align: top; padding-top: 8px !important;">
                        <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                            <tr>
                                <td style="font-weight: bold; background: #f8f9fa; width: 45%; border: 1px solid #dee2e6; padding: 3px 6px;">PO DATE:</td>
                                <td style="text-align: right; border: 1px solid #dee2e6; padding: 3px 6px;"><?= htmlspecialchars($header['podate'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; background: #f8f9fa; border: 1px solid #dee2e6; padding: 3px 6px;">DELIVERY DUE:</td>
                                <td style="text-align: right; border: 1px solid #dee2e6; padding: 3px 6px;"><?= htmlspecialchars($header['podateline'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; background: #f8f9fa; border: 1px solid #dee2e6; padding: 3px 6px;">CURRENCY:</td>
                                <td style="text-align: right; font-weight: bold; border: 1px solid #dee2e6; padding: 3px 6px;"><?= htmlspecialchars($header['pocurr'] ?? 'MYR') ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; background: #f8f9fa; border: 1px solid #dee2e6; padding: 3px 6px;">STATUS:</td>
                                <td style="text-align: right; border: 1px solid #dee2e6; padding: 3px 6px;"><?= htmlspecialchars($header['postts'] ?? 'Open') ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Row 4: Column Header Labels -->
                <tr style="background-color: #f1f4f9;">
                    <th style="width: 5%; text-align: center; font-size: 11px;">#</th>
                    <th style="width: 50%; font-size: 11px;">Item Description</th>
                    <th style="width: 15%; text-align: right; font-size: 11px;">Qty</th>
                    <th style="width: 15%; text-align: right; font-size: 11px;">Unit Price</th>
                    <th style="width: 15%; text-align: right; font-size: 11px;">Amount</th>
                </tr>
            </thead>

            <tbody>
                <?php 
                $grandTotal = 0;
                if (!empty($items)):
                    foreach ($items as $idx => $row): 
                        $qty = (float)($row['podqty'] ?? 0);
                        $price = (float)($row['podprice'] ?? 0);
                        $amount = $qty * $price;
                        $grandTotal += $amount;
                ?>
                <tr>
                    <td style="text-align: center; color: #6c757d;"><?= $idx + 1 ?></td>
                    <td>
                        <div style="font-weight: bold;"><?= htmlspecialchars($row['prodname'] ?? $row['poditm'] ?? '') ?></div>
                        <?php if (!empty($row['prodname']) && !empty($row['poditm'])): ?>
                            <small style="color: #6c757d;">Item Code: <?= htmlspecialchars($row['poditm']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;"><?= number_format($qty, 2) ?> <?= htmlspecialchars($row['poduom'] ?? '') ?></td>
                    <td style="text-align: right;"><?= number_format($price, 4) ?></td>
                    <td style="text-align: right; font-weight: bold;"><?= number_format($amount, 2) ?></td>
                </tr>
                <?php 
                    endforeach; 
                ?>
                
                <!-- Grand Total row inside tbody: only prints once at the bottom -->
                <tr style="page-break-inside: avoid;">
                    <td colspan="4" style="text-align: right; font-weight: bold; padding: 8px;">
                        GRAND TOTAL (<?= htmlspecialchars($header['pocurr'] ?? 'MYR') ?>):
                    </td>
                    <td style="text-align: right; font-weight: bold; color: #0d6efd; font-size: 14px; padding: 8px;">
                        <?= number_format($grandTotal, 2) ?>
                    </td>
                </tr>

                <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #6c757d;">No line items found for this purchase order.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Last-Page Notice & Signatures -->
        <div class="po-footer-section">
            <div class="notice-box mb-4">
                <strong>IMPORTANT INSTRUCTIONS / TERMS & CONDITIONS:</strong>
                <ol class="mb-0 ps-3 mt-1" style="line-height: 1.4;">
                    <li>Please acknowledge receipt of this PO and confirm delivery dateline within 2 working days.</li>
                    <li>Delivery invoice and packing list must quote the above Purchase Order Number (<?= htmlspecialchars($header['pono'] ?? '') ?>).</li>
                    <li>Goods received are subject to physical inspection and approval upon arrival at warehouse.</li>
                </ol>
            </div>

            <div class="signature-box">
                <div class="sign-line">
                    Prepared By / Purchasing Dept
                </div>
                <div class="sign-line">
                    Authorized Signatory & Stamp
                </div>
            </div>
        </div>

    </div>

</body>
</html>