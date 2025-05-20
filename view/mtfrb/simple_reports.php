<!DOCTYPE html>
<html>
<head>
    <title>Reports Table</title>
    <style>
        .table-container {
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Reported Name</th>
                    <th>Complaint</th>
                    <th>Reason</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample data - replace with PHP loop -->
                <tr>
                    <td>John Doe</td>
                    <td>Service Quality</td>
                    <td>Delayed service</td>
                    <td>05-19-2025</td>
                </tr>
                <tr>
                    <td>Jane Smith</td>
                    <td>Driver Behavior</td>
                    <td>Rude behavior</td>
                    <td>05-18-2025</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
