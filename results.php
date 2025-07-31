<?php
// results.php
require_once 'functions.php';
start_secure_session();
if (!is_logged_in()) {
    redirect('login.php');
}

$logged_in_user_id = $_SESSION['user_id'];
$logged_in_user_name = $_SESSION['user_name'] ?? 'User';

$processing_message = $_SESSION['processing_message'] ?? '';
$processing_error = $_SESSION['processing_error'] ?? false;
$python_debug_info = $_SESSION['python_debug_info'] ?? '';
unset($_SESSION['processing_message'], $_SESSION['processing_error'], $_SESSION['python_debug_info']);

$forecast_data = null;
$forecast_file_path = 'forecast_result.json';

if (file_exists($forecast_file_path)) {
    $json_content = file_get_contents($forecast_file_path);
    if ($json_content !== false) {
        $forecast_data = json_decode($json_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $processing_message = "Error: Could not parse forecast results. Invalid JSON format.";
            $processing_error = true;
            error_log("JSON decode error in results.php: " . json_last_error_msg());
        }
    } else {
        $processing_message = "Error: Could not read forecast results file.";
        $processing_error = true;
        error_log("Failed to read forecast_result.json in results.php");
    }
} else {
    if (empty($processing_message)) {
         $processing_message = "No forecast results available yet. Please upload data and process it first.";
    }
}

$chart_data = [
    'labels' => [],
    'lr_data' => [],
    'rf_data' => [],
    'mae_lr' => 0,
    'rmse_lr' => 0,
    'r2_lr' => 0,
    'mae_rf' => 0,
    'rmse_rf' => 0,
    'r2_rf' => 0
];

// Helper function to generate suggestions based on predicted vs actual sales
function generate_suggestion($input_sales, $predicted) {
    if ($input_sales == 0) return "No current sales data.";
    $diff_percent = (($predicted - $input_sales) / $input_sales) * 100;
    if ($diff_percent > 20) {
        return "Consider increasing stock & marketing to meet higher demand.";
    } elseif ($diff_percent > 5) {
        return "Monitor sales closely; slight increase expected.";
    } elseif ($diff_percent >= -5) {
        return "Maintain current stock levels.";
    } elseif ($diff_percent >= -20) {
        return "Evaluate stock to reduce overstock risk.";
    } else {
        return "Consider discount/promotion to clear excess stock.";
    }
}

if ($forecast_data && !$processing_error) {
    $lr_results = $forecast_data['forecasts']['Linear Regression'] ?? [];
    $rf_results = $forecast_data['forecasts']['Random Forest'] ?? [];

    if (!empty($lr_results)) {
        $first_lr = $lr_results[0];
        $chart_data['mae_lr'] = $first_lr['MAE'] ?? 0;
        $chart_data['rmse_lr'] = $first_lr['RMSE'] ?? 0;
        $chart_data['r2_lr'] = $first_lr['R2'] ?? 0;
    }
    if (!empty($rf_results)) {
        $first_rf = $rf_results[0];
        $chart_data['mae_rf'] = $first_rf['MAE'] ?? 0;
        $chart_data['rmse_rf'] = $first_rf['RMSE'] ?? 0;
        $chart_data['r2_rf'] = $first_rf['R2'] ?? 0;
    }

    $max_products_for_chart = 10;
    $product_count = 0;
    foreach ($lr_results as $item) {
        if ($product_count >= $max_products_for_chart) break;
        $chart_data['labels'][] = $item['Product_Name'] ?? 'Unknown';
        $chart_data['lr_data'][] = round($item['Predicted_Demand'] ?? 0, 2);
        $product_count++;
    }

    $product_count = 0;
    foreach ($rf_results as $item) {
        if ($product_count >= $max_products_for_chart) break;
        $chart_data['rf_data'][] = round($item['Predicted_Demand'] ?? 0, 2);
        $product_count++;
    }

    // Add suggestions dynamically to each item
    foreach ($lr_results as &$item) {
        $input_sales = floatval($item['Input_Monthly_Sales'] ?? 0);
        $predicted = floatval($item['Predicted_Demand'] ?? 0);
        $item['Suggestion'] = generate_suggestion($input_sales, $predicted);
    }
    unset($item);
    foreach ($rf_results as &$item) {
        $input_sales = floatval($item['Input_Monthly_Sales'] ?? 0);
        $predicted = floatval($item['Predicted_Demand'] ?? 0);
        $item['Suggestion'] = generate_suggestion($input_sales, $predicted);
    }
    unset($item);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Forecast Results - Inventory Demand Forecasting</title>
<link rel="stylesheet" href="styles.css" />
<style>
/* Inline styles for brevity, you can keep your styles.css if preferred */
body {font-family: Arial,sans-serif; margin:0; padding:0; background:#f4f4f4;}
.navbar {background:#343a40; overflow:hidden; padding:0; margin:0; color:#fff;}
.navbar ul {list-style:none; margin:0; padding:0;}
.navbar li {float:left;}
.navbar li a, .navbar li span {display:block; color:#fff; text-align:center; padding:14px 20px; text-decoration:none;}
.navbar li a:hover {background:#555;}
.navbar li.logout {float:right;}
.main-content {padding:20px; max-width:1200px; margin:20px auto; background:#fff; border-radius:5px; box-shadow:0 0 10px rgba(0,0,0,.1);}
.section {margin-bottom:30px;}
.section h2 {color:#333; border-bottom:2px solid #007bff; padding-bottom:5px;}
.message {padding:15px; margin:15px 0; border-radius:4px;}
.message.info {background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb;}
.message.success {background:#d4edda; color:#155724; border:1px solid #c3e6cb;}
.message.error {background:#f8d7da; color:#721c24; border:1px solid #f5c6cb;}
.debug-info {font-family: monospace; white-space: pre-wrap; background:#f8f9fa; border:1px solid #dee2e6; padding:10px; margin-top:10px; max-height:200px; overflow-y:auto; font-size:.8rem;}
table {width:100%; border-collapse:collapse; margin-top:10px;}
th, td {border:1px solid #ddd; padding:8px; text-align:left; vertical-align:middle;}
th {background:#f2f2f2; position:sticky; top:0;}
tr:nth-child(even) {background:#f9f9f9;}
tr:hover {background:#f5f5f5;}
.metrics-grid {display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:15px; margin-top:15px;}
.metric-card {border:1px solid #ddd; border-radius:5px; padding:15px; text-align:center; background:#f8f9fa;}
.metric-card h3 {margin-top:0; margin-bottom:10px; font-size:1rem; color:#495057;}
.metric-value {font-size:1.5rem; font-weight:bold;}
.lr-color {color:#007bff;}
.rf-color {color:#28a745;}
.chart-container {margin-top:20px; position:relative; height:400px;}
.navbar::after {content:""; display:table; clear:both;}
@media screen and (max-width:600px) {
    .navbar li {float:none;}
    .navbar li.logout {float:none; text-align:center;}
    .metrics-grid {grid-template-columns:1fr;}
}
</style>
</head>
<body>

<nav class="navbar">
    <ul>
        <li><a href="dashboard.php">Inventory Forecast</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="upload_data.php">Upload Data</a></li>
        <li><a href="results.php">View Results</a></li>
        <li class="logout"><a href="logout.php">Logout (<?php echo htmlspecialchars($logged_in_user_name); ?>)</a></li>
    </ul>
</nav>

<div class="main-content">

    <div class="section">
        <h2>Forecast Processing Status</h2>
        <?php if (!empty($processing_message)): ?>
            <div class="message <?php echo $processing_error ? 'error' : (strpos($processing_message, 'successfully') !== false ? 'success' : 'info'); ?>">
                <?php echo nl2br(htmlspecialchars($processing_message)); ?>
            </div>
            <?php if ($processing_error && !empty($python_debug_info)): ?>
                <details>
                    <summary style="cursor: pointer; color: #dc3545;">Show Debug Information</summary>
                    <div class="debug-info"><?php echo htmlspecialchars($python_debug_info); ?></div>
                </details>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if ($forecast_data && !$processing_error): ?>
        <div class="section">
            <h2>Model Performance Metrics</h2>
            <p>These metrics compare the models' predictions against the input <code>Monthly_Sales</code> from your uploaded data.</p>
            <div class="metrics-grid">
                <div class="metric-card">
                    <h3 class="lr-color">Linear Regression</h3>
                    <p>MAE: <span class="metric-value lr-color"><?php echo number_format($chart_data['mae_lr'], 4); ?></span></p>
                    <p>RMSE: <span class="metric-value lr-color"><?php echo number_format($chart_data['rmse_lr'], 4); ?></span></p>
                    <p>R²: <span class="metric-value lr-color"><?php echo number_format($chart_data['r2_lr'], 4); ?></span></p>
                </div>
                <div class="metric-card">
                    <h3 class="rf-color">Random Forest</h3>
                    <p>MAE: <span class="metric-value rf-color"><?php echo number_format($chart_data['mae_rf'], 4); ?></span></p>
                    <p>RMSE: <span class="metric-value rf-color"><?php echo number_format($chart_data['rmse_rf'], 4); ?></span></p>
                    <p>R²: <span class="metric-value rf-color"><?php echo number_format($chart_data['r2_rf'], 4); ?></span></p>
                </div>
            </div>
            <p style="margin-top: 15px;"><strong>Note:</strong> These metrics evaluate how well the models fit the data you just uploaded. True forecast accuracy (predicting next month's sales based on current data) can only be determined when actual sales data becomes available.</p>
        </div>

        <div class="section">
            <h2>Demand Forecast Comparison (Top <?php echo min(10, count($chart_data['labels'])); ?> Products)</h2>
            <p>Predicted demand for the month following <strong><?php echo htmlspecialchars($lr_results[0]['Input_Time'] ?? 'N/A'); ?></strong>.</p>
            <div class="chart-container">
                <canvas id="forecastChart"></canvas>
            </div>
        </div>

        <div class="section">
            <h2>Detailed Forecast Results</h2>
            <p>Full list of predicted demands for all products in your upload.</p>
            
            <h3>Linear Regression Predictions</h3>
            <div style="max-height: 400px; overflow-y: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Input Sales (<?php echo htmlspecialchars($lr_results[0]['Input_Time'] ?? 'Month'); ?>)</th>
                        <th>Price ($)</th>
                        <th>Stock Level</th>
                        <th>Promotion</th>
                        <th>Predicted Demand (<?php echo htmlspecialchars($lr_results[0]['Forecasted_Time'] ?? 'Next Month'); ?>)</th>
                        <th>Suggestion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lr_results as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['Product_ID'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['Product_Name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['Category'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['Input_Monthly_Sales'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(number_format($item['Price ($)'] ?? 0, 2)); ?></td>
                        <td><?php echo htmlspecialchars($item['Stock_Level'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['Promotion'] ?? 'N/A'); ?></td>
                        <td><strong class="lr-color"><?php echo htmlspecialchars(number_format($item['Predicted_Demand'] ?? 0, 2)); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['Suggestion'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <h3 style="margin-top: 30px;">Random Forest Predictions</h3>
            <div style="max-height: 400px; overflow-y: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Input Sales (<?php echo htmlspecialchars($rf_results[0]['Input_Time'] ?? 'Month'); ?>)</th>
                        <th>Price ($)</th>
                        <th>Stock Level</th>
                        <th>Promotion</th>
                        <th>Predicted Demand (<?php echo htmlspecialchars($rf_results[0]['Forecasted_Time'] ?? 'Next Month'); ?>)</th>
                        <th>Suggestion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rf_results as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['Product_ID'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['Product_Name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['Category'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['Input_Monthly_Sales'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(number_format($item['Price ($)'] ?? 0, 2)); ?></td>
                        <td><?php echo htmlspecialchars($item['Stock_Level'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['Promotion'] ?? 'N/A'); ?></td>
                        <td><strong class="rf-color"><?php echo htmlspecialchars(number_format($item['Predicted_Demand'] ?? 0, 2)); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['Suggestion'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>

    <?php elseif (!$processing_error && empty($processing_message)): ?>
         <div class="section">
             <h2>No Results Available</h2>
             <p>It seems you haven't generated any forecasts yet.</p>
             <a href="upload_data.php" class="btn">Upload Data to Get Started</a>
         </div>
    <?php endif; ?>
</div>

<script>
    var chartData = <?php echo json_encode($chart_data); ?>;
    var forecastGeneratedAt = <?php echo json_encode($forecast_data['generated_at'] ?? 'N/A'); ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="chart.js"></script>

</body>
</html>
