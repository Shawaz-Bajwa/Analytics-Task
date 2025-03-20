<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .filter-box {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .chart-container {
            position: relative;
            width: 100%;
            height: 400px;
        }

        canvas {
            width: 100% !important;
            height: 100% !important;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1 class="text-center">Analytics Dashboard</h1>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Add Data Form -->
        <form action="{{ route('analytics.store') }}" method="POST" class="mb-4">
            @csrf
            <div class="row">
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="profile_views" class="form-control" placeholder="Profile Views"
                        min="0" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="visitors" class="form-control" placeholder="Visitors" min="0"
                        required>
                </div>
                <div class="col-md-3">
                    <select name="name" class="form-control" required>
                        <option value="">Select Platform</option>
                        <option value="google_analytics">Google Analytics</option>
                        <option value="microsoft_clarity">Microsoft Clarity</option>
                        <option value="facebook">Facebook</option>
                        <option value="instagram">Instagram</option>
                        <option value="snapchat">Snapchat</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </div>
        </form>

        <!-- Data Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Profile Views</th>
                    <th>Visitors</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $item->name)) }}</td>
                        <td>{{ $item->date }}</td>
                        <td>{{ $item->profile_views }}</td>
                        <td>{{ $item->visitors }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editData({{ $item->id }})">Edit</button>
                            <form action="{{ route('analytics.delete', $item->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this record?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Analytics Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editForm" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-body">
                            <input type="hidden" id="editId">
                            <div class="mb-3">
                                <label>Date</label>
                                <input type="date" id="editDate" name="date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Profile Views</label>
                                <input type="number" id="editProfileViews" name="profile_views" class="form-control"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label>Visitors</label>
                                <input type="number" id="editVisitors" name="visitors" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Name</label>
                                <select id="editName" name="name" class="form-control" required>
                                    <option value="">Select Platform</option>
                                    <option value="google_analytics">Google Analytics</option>
                                    <option value="microsoft_clarity">Microsoft Clarity</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="snapchat">Snapchat</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Date Filters -->
        <div class="filter-box">
            <div>
                <label for="startDate" class="form-label">Start Date:</label>
                <input type="date" id="startDate" class="form-control">
            </div>
            <div>
                <label for="endDate" class="form-label">End Date:</label>
                <input type="date" id="endDate" class="form-control">
            </div>
            <div class="d-flex align-items-end">
                <button class="btn btn-primary" onclick="filterData()">Filter</button>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        let data = @json($data);

        function aggregateData(filteredData) {
            const aggregatedData = {};

            filteredData.forEach(item => {
                const name = formatName(item.name);
                if (!aggregatedData[name]) {
                    aggregatedData[name] = {
                        visitors: 0,
                        profile_views: 0
                    };
                }
                aggregatedData[name].visitors += item.visitors;
                aggregatedData[name].profile_views += item.profile_views;
            });

            return aggregatedData;
        }

        function formatName(name) {
            return name.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
        }

        function filterData() {
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);

            if (isNaN(startDate) || isNaN(endDate)) {
                alert("Please select valid start and end dates.");
                return;
            }

            const filteredData = data.filter(item => {
                const itemDate = new Date(item.date);
                return itemDate >= startDate && itemDate <= endDate;
            });

            if (filteredData.length === 0) {
                alert("No data available for the selected date range.");
                return;
            }

            updateCharts(aggregateData(filteredData));
        }

        function updateCharts(aggregatedData) {
            const labels = Object.keys(aggregatedData);
            const visitors = labels.map(label => aggregatedData[label].visitors);
            const profileViews = labels.map(label => aggregatedData[label].profile_views);

            lineChart.data.labels = labels;
            lineChart.data.datasets[0].data = visitors;
            lineChart.data.datasets[1].data = profileViews;
            lineChart.update();

            pieChart.data.labels = labels;
            pieChart.data.datasets[0].data = visitors;
            pieChart.update();
        }

        const aggregatedData = aggregateData(data);
        const labels = Object.keys(aggregatedData);
        const visitors = labels.map(label => aggregatedData[label].visitors);
        const profileViews = labels.map(label => aggregatedData[label].profile_views);

        // Line Chart
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        const lineChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Visitors',
                        data: visitors,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.2)',
                        borderWidth: 2,
                        fill: true
                    },
                    {
                        label: 'Profile Views',
                        data: profileViews,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        borderWidth: 2,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Pie Chart
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        const pieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Visitors Distribution',
                    data: visitors,
                    backgroundColor: [
                        '#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6c757d'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
    <script>
        function editData(id) {
            $.get('/analytics/edit/' + id, function(data) {
                $('#editId').val(data.id);
                $('#editDate').val(data.date);
                $('#editProfileViews').val(data.profile_views);
                $('#editVisitors').val(data.visitors);
                $('#editName').val(data.name);
                $('#editForm').attr('action', '/analytics/update/' + id);
                $('#editModal').modal('show');
            });
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>
