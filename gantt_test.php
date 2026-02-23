<!DOCTYPE html>
<html>
<head>
    <title>Gantt Test</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['gantt']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Task ID');
            data.addColumn('string', 'Task Name');
            data.addColumn('string', 'Resource');
            data.addColumn('date', 'Start Date');
            data.addColumn('date', 'End Date');
            data.addColumn('number', 'Duration');
            data.addColumn('number', 'Percent Complete');
            data.addColumn('string', 'Dependencies');

            data.addRows([
                ['Task1', 'Task A', 'Resource A', new Date(2025, 0, 1), new Date(2025, 0, 5), null, 100, null],
                ['Task2', 'Task B', 'Resource B', new Date(2025, 0, 6), new Date(2025, 0, 10), null, 50, null],
                ['Task3', 'Task C', 'Resource C', new Date(2025, 0, 3), new Date(2025, 0, 8), null, 0, null]
            ]);

            var options = {
                height: 275,
                gantt: {
                    // Keep it minimal
                }
            };

            var chart = new google.visualization.Gantt(document.getElementById('chart_div'));
            chart.draw(data, options);
        }
    </script>
</head>
<body>
    <div id="chart_div" style="width: 900px; height: 275px;"></div>
</body>
</html>