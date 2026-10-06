<!DOCTYPE html>
<html>
<head>
    <title>Low Stock Alert</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-w-xl; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-top: 4px solid #d9534f;">
        <h2 style="color: #d9534f;">Low Stock Alert</h2>
        
        <p>Attention Purchasing Team,</p>
        
        <p>The following item has dropped below the minimum healthy stock threshold (10 units) and requires immediate restocking:</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; background-color: #f9f9f9;">Product Name</td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $product->name }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; background-color: #f9f9f9;">SKU</td>
                <td style="padding: 10px; border: 1px solid #ddd;">{{ $product->sku }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; background-color: #f9f9f9;">Current Stock</td>
                <td style="padding: 10px; border: 1px solid #ddd; color: #d9534f; font-weight: bold;">{{ $product->stock_quantity }} units</td>
            </tr>
        </table>
        
        <p>Please log into the Jubis ERP Admin Panel to review the inventory and generate a new Purchase Order for the supplier.</p>
        
        <br>
        <p><i>This is an automated message from the Jubis Marketing ERP System.</i></p>
    </div>
</body>
</html>
