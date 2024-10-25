<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos factures</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
            background-color: #0a0a0a;
            color: #fff;
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            width: 100%;
            background-color: #1a1a1a;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        h1 {
            font-size: 32px;
            color: #fff;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 700;
        }
        .invoice-card {
            background-color: #262626;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .invoice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        .invoice-number {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .invoice-amount {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #3ecf8e;
        }
        .invoice-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .status-paid {
            background-color: #3ecf8e;
            color: #0a0a0a;
        }
        .status-open {
            background-color: #ffc107;
            color: #0a0a0a;
        }
        .invoice-date, .payment-info {
            margin-top: 15px;
            font-size: 14px;
            color: #bbb;
            line-height: 1.6;
        }
        .invoice-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #3ecf8e;
            color: #0a0a0a;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .invoice-link:hover {
            background-color: #34b97b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Vos factures</h1>
        <?php foreach ($invoices as $invoice): ?>
            <div class="invoice-card">
                <div class="invoice-number"><?php echo htmlspecialchars($invoice['number']); ?></div>
                <div class="invoice-amount"><?php echo htmlspecialchars($invoice['amount_due']) . ' ' . htmlspecialchars($invoice['currency']); ?></div>
                <span class="invoice-status status-<?php echo $invoice['status']; ?>"><?php echo htmlspecialchars($invoice['status']); ?></span>
                <div class="invoice-date">
                    Créée le : <?php echo htmlspecialchars($invoice['created']); ?><br>
                    Due le : <?php echo htmlspecialchars($invoice['due_date']); ?>
                </div>
                <div class="payment-info">
                    Carte : <?php echo htmlspecialchars($invoice['card_brand']); ?> **** <?php echo htmlspecialchars($invoice['last_four_digits']); ?>
                </div>
                <a href="<?php echo htmlspecialchars($invoice['pdf_url']); ?>" class="invoice-link" target="_blank">Télécharger PDF</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>