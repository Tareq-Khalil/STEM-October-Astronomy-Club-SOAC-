<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Report</title>
    <style>
        body {
            background-color: #f9fafb;
            font-family: Arial, sans-serif;
            padding: 1rem;
        }
        .container {
            max-width: 1000px;
            margin: auto;
        }
        .header {
            text-align: center;
            margin-bottom: 1rem;
        }
        .header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 0.3rem;
        }
        .header p {
            font-size: 0.9rem;
            color: #4b5563;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.8rem;
            margin-bottom: 1rem;
        }
        .card {
            background-color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
        }
        .card h2 {
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .card p {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .income { border-color: #10b981; color: #10b981; }
        .expense { border-color: #ef4444; color: #ef4444; }
        .net { border-color: #3b82f6; color: #3b82f6; }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 0.8rem;
        }
        .category-card {
            background-color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .category-card h2 {
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .category-item {
            display: flex;
            justify-content: space-between;
            background-color: #f9fafb;
            padding: 0.5rem;
            border-radius: 0.3rem;
            font-size: 0.875rem;
        }
        .green { color: #10b981; }
        .red { color: #ef4444; }
    </style>
</head>
<body>

<div class="container">
    <!-- Header Section -->
    <div class="header">
        <h1>Financial Report</h1>
        <p>Hello <strong>{{ $account->name }}</strong>,</p>
        <p>Here's your financial summary:</p>
        <p style="background-color: #f3f4f6; padding: 0.3rem 0.6rem; border-radius: 0.3rem; display: inline-block; font-size: 0.875rem;">
            Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
        </p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="card income">
            <h2>Total Income</h2>
            <p>Rs.{{ number_format($totalIncome, 2) }}</p>
        </div>
        <div class="card expense">
            <h2>Total Expenses</h2>
            <p>Rs.{{ number_format($totalExpenses, 2) }}</p>
        </div>
        <div class="card net">
            <h2>Net Income</h2>
            <p>Rs.{{ number_format($net, 2) }}</p>
        </div>
    </div>

    <!-- Income & Expense by Category -->
    <div class="category-grid">
        <div class="category-card">
            <h2>Income by Category</h2>
            @foreach($incomeCategories as $category => $amount)
            <div class="category-item">
                <span>{{ $category }}</span>
                <span class="green">Rs.{{ number_format($amount, 2) }}</span>
            </div>
            @endforeach
        </div>

        <div class="category-card">
            <h2>Expenses by Category</h2>
            @foreach($expenseCategories as $category => $amount)
            <div class="category-item">
                <span>{{ $category }}</span>
                <span class="red">Rs.{{ number_format($amount, 2) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

</body>
</html>
