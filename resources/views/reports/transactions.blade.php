<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Report</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; /* Reduce font size */
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 4px; /* Reduce padding to shrink row height */
            text-align: left; 
            font-size: 10px; /* Further reduce font size */
        }
        th { 
            background-color: #f2f2f2; 
            font-size: 11px; /* Slightly larger for headers */
        }
    </style>
</head>
<body>
    <h1>Transaction Report</h1>
    <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    @foreach(array_keys($columns) as $column)
                        <td>{{ $transaction->$column }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
