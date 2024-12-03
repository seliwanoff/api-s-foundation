<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users List</title>
  <style>
  body {
    font-family: Arial, sans-serif;
    margin: 20px;
    color: #333;
  }

  h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #0d0c22;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }

  th,
  td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
  }

  th {
    background-color: #f4f4f4;
    color: #333;
  }

  tr:nth-child(even) {
    background-color: #f9f9f9;
  }

  tr:hover {
    background-color: #f1f1f1;
  }

  .footer {
    margin-top: 30px;
    text-align: center;
    font-size: 12px;
    color: #666;
  }
  </style>
</head>

<body>
  <h1>Users List</h1>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Gender</th>
        <th>Account Number</th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $index => $user)
      <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $user->first_name }}</td>
        <td>{{ $user->email }}</td>
        <td>{{ $user->sex }}</td>
        <td>{{ $user->account_number }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    Generated on {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}
  </div>
</body>

</html>