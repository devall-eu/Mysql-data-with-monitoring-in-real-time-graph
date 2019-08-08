<!DOCTYPE html>
<html lang="en">

<head>
    <link type="text/css" rel="stylesheet" href="/assets/css/bootstrap.min.css"/>
    <link type="text/css" rel="stylesheet" href="/assets/css/morris.css"/>
    <link type="text/css" rel="stylesheet" href="/assets/css/monitoring.css"/>
    <script type="text/javascript" src="/assets/js/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="/assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/assets/js/morris.js"></script>
    <script type="text/javascript" src="/assets/js/raphael.min.js"></script>
</head>
<body>

<div class="container">
    <div id="loadpage">
        <img id="loading" src="/assets/loading.gif" alt="loading"/>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 titlenone">
            <div class="table" style="display:none">
                <table id="bytesSent" style="display: none;"></table>
                <table id="bytesReceived" style="display: none;"></table>
                <table id="connections" style="display: none;"></table>
            </div>
            <div>
                <h3>Bytes Received / Sent</h3>
                <div class="table-responsive">
                    <table class="table table-striped small">
                        <tr id="time1"></tr>
                        <tr id="bytesSentmonitor"></tr>
                        <tr id="bytesReceivedmonitor"></tr>
                    </table>
                </div>
                <br>
                <div id="graph">
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-12 titlenone">
            <div>
                <h3>Connections / Process</h3>
                <div class="table-responsive">
                    <table class="table table-striped small">
                        <tr id="time2"></tr>
                        <tr id="connectionsmonitor"></tr>
                        <tr id="processmonitor"></tr>
                    </table>
                </div>
                <br>
                <div id="graph2">
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 titlenone">
            <div>
                <h3>Common commands</h3>
                <div class="table-responsive">
                    <table class="table small">
                        <tr id="select"></tr>
                        <tr id="insert"></tr>
                        <tr id="update"></tr>
                        <tr id="delete"></tr>
                    </table>
                </div>
                <br>
                <div id="graph3">
                </div>
            </div>
        </div>
        <div id="slowq" class="col-lg-12 col-md-12 titlenone">
            <div>
                <h3>Slow Queries
                    <small>(Over <?php echo $params['slowqueries']; ?> seconds)</small>
                </h3>
                <div class="table-responsive">
                    <table class="table table-striped small">
                        <tbody id="slow"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    let count = 0;
    let array = [];
    let array2 = [];

    let receivedsentcolumns = '<?php echo $params['receivedsentcolumns']; ?>';
    let connesctionsgraphcolumns = '<?php echo $params['connectionsgraphcolumns']; ?>';
    let connesctionscolumns = '<?php echo $params['connectionscolumns']; ?>';
    let receivedsentgraphcolumns = '<?php echo $params['receivedsentgraphcolumns']; ?>';

    setInterval(function () {
        $.ajax({
            type: 'POST',
            url: 'mysqlmonitoring/getData',
            data: {
                slowqueries: '<?php echo $params['slowqueries']; ?>',
        /*
         * If in config is set csrf token to TRUE
         * Send the token name and token value
         * If is set to FALSE then don't send this
         */
        <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
    },
        dataType: "json",
            success: function (data) {

            /* Get time */
            /* now() */
            /* timestamp for graph */
            /* @type {Date} */
            let now = new Date(Date.now());
            let timestamp = new Date().getTime();
            let formatted = now.getHours().toLocaleString() + ":" + now.getMinutes().toLocaleString() + ":" + now.getSeconds().toLocaleString();

            /* bytes Sent */
            $("#bytesSent").append('<tr><td>' + data.bytesSent + '</td></tr>');
            let actualbytesSent = data.bytesSent;
            let lastbytesSent = $('#bytesSent tr:last').prev().children('td').text();
            let bytesSent = actualbytesSent - lastbytesSent;

            /* bytes Received */
            $("#bytesReceived").append('<tr><td>' + data.bytesReceived + '</td></tr>');
            let actualbytesReceived = data.bytesReceived;
            let lastbytesReceived = $('#bytesReceived tr:last').prev().children('td').text();
            let bytesReceived = actualbytesReceived - lastbytesReceived;

            /* Connections */
            $("#connections").append('<tr><td>' + data.Connections + '</td></tr>');
            let actualconnections = data.Connections;
            let lastconnections = $('#connections tr:last').prev().children('td').text();
            let connections = actualconnections - lastconnections;

            /* Process */
            let process = data.process;

            /* Skipped first row */
            if (count > 0) {
                /* Fill array for graph */
                array[count - 1] = [timestamp, bytesSent, bytesReceived];
                array2[count - 1] = [formatted, connections, process];
            }

            /* Max. column */
            /* Delete first one */
            /* Then script add new one */
            if (count > receivedsentcolumns) {
                $("#time1 td:first").next().next().remove();
                $("#bytesSentmonitor td:first").next().next().remove();
                $("#bytesReceivedmonitor td:first").next().next().remove();
                $("#bytesSent td:first").remove();
                $("#bytesReceived td:first").remove();
            }

            /* Max. column */
            /* Delete first one */
            /* Then script add new one */
            if (count > connesctionscolumns) {
                $("#time2 td:first").next().next().remove();
                $("#connectionsmonitor td:first").next().next().remove();
                $("#connections td:first").remove();
                $("#processmonitor td:first").next().next().remove();
            }

            /* On graph delete first row */
            /* The chart has more data than in the table */
            if (count > connesctionsgraphcolumns) {
                array2.shift();
                array2 = array2.filter(function (e) {
                    return e
                });
            }

            /* On graph delete first row */
            /* The chart has more data than in the table */
            if (count > receivedsentgraphcolumns) {
                array.shift();
                array = array.filter(function (e) {
                    return e
                });
            }

            /* Add new column to the end */
            /* Add for all 3 rows */
            if (count > 0) {
                /* First time add <td> with text */
                if (count === 1) {
                    $("#time1").append('<td style="background: #28a745;"></td><td>Time</td>');
                    $("#time2").append('<td style="background: #28a745;"></td><td>Time</td>');
                    $("#bytesSentmonitor").append('<td style="background: #7e95a6;"></td><td>Sent</td>');
                    $("#bytesReceivedmonitor").append('<td style="background: #0b62a4;"></td><td>Received</td>');
                    $("#connectionsmonitor").append('<td style="background: #0b62a4;"></td><td>Connections</td>');
                    $("#processmonitor").append('<td style="background: #7e95a6;"></td><td>Process</td>');

                    /* Show titles and add some style */
                    $('.titlenone').css('display', 'block');
                    $('.jmb').attr('class', 'jumbotron');
                }

                /* Append data to the tables */
                $("#time1").append('<td class="small">' + formatted + '</td>');
                $("#time2").append('<td class="small">' + formatted + '</td>');
                $("#bytesSentmonitor").append('<td class="small">' + formatBytes(bytesSent, 2) + '</td>');
                $("#bytesReceivedmonitor").append('<td class="small">' + formatBytes(bytesReceived, 2) + '</td>');
                $("#connectionsmonitor").append('<td>' + connections + '</td>');
                $("#processmonitor").append('<td>' + process + '</td>');

                /* Prepare for array with slow queries */
                $(data.slow).each(function () {
                    $('#slow').append('<tr><td>' + $(this)[0].Info + '</td><td>' + $(this)[0].Time + ' seconds</td></tr>');
                });

                /* Common commands statistics */
                $("#insert").empty();
                $("#select").empty();
                $("#update").empty();
                $("#delete").empty();

                $("#select").append('<td style="background: #7e95a6;"></td><td>SELECT</td><td>' + data.Com_select + '</td>');
                $("#insert").append('<td style="background: #0b62a4;"></td><td>INSERT</td><td>' + data.Com_insert + '</td>');
                $("#update").append('<td style="background: #7e95a6;"></td><td>UPDATE</td><td>' + data.Com_update + '</td>');
                $("#delete").append('<td style="background: #0b62a4;"></td><td>DELETE</td><td>' + data.Com_delete + '</td>');
            }

            /* For Raphael line graph */
            /* Array */
            let timestamp_data = [];
            let timestamp_data2 = [];

            /* Fill array for graph data */
            /* Two dimensional array */
            $(array).each(function () {
                if ($(this).length > 1) {
                    timestamp_data.push({
                        "period": new Date($(this)[0]).getTime(),
                        "bytessent": ($(this)[1] / 1024).toFixed(2),
                        "bytesreceived": ($(this)[2] / 1024).toFixed(2)
                    });
                }
            });

            /* Fill array for graph data */
            /* Two dimensional array */
            $(array2).each(function () {
                if ($(this).length > 1) {
                    timestamp_data2.push({
                        "period": $(this)[0],
                        "connections": $(this)[1],
                        "process": $(this)[2]
                    });
                }
            });

            /* Clear data */
            if (count > 0) $('#loadpage').empty();
            if (count > 0) $('#graph').empty();
            if (count > 0) $('#graph2').empty();
            if (count > 0) $('#graph3').empty();

            /* Draw a Morris graph */
            if (count > 0) {
                Morris.Line({
                    element: 'graph',
                    data: timestamp_data,
                    xkey: 'period',
                    ykeys: ['bytesreceived', 'bytessent'],
                    labels: ['Bytes received', 'Bytes sent'],
                    dateFormat: function (x) {
                        return new Date(x).toLocaleTimeString();
                    }
                });

                Morris.Bar({
                    element: 'graph2',
                    data: timestamp_data2,
                    xkey: 'period',
                    ykeys: ['connections', 'process'],
                    labels: ['Connections', 'Process'],
                    dateFormat: function (x) {
                        return new Date(x).toLocaleTimeString();
                    }
                });

                let commandsum = parseInt(data.Com_select) + parseInt(data.Com_insert) + parseInt(data.Com_update) + parseInt(data.Com_delete);

                let Data = [
                    {label: "SELECT", value: data.Com_select},
                    {label: "INSERT", value: data.Com_insert},
                    {label: "UPDATE", value: data.Com_update},
                    {label: "DELETE", value: data.Com_delete}
                ];

                Morris.Donut({
                    element: 'graph3',
                    data: Data,
                    formatter: function (value) {
                        return Math.floor(value / commandsum * 100) + '%';
                    }
                });
            }
            count++;
        }
    })
    }, <?php echo $params['refreshtime']; ?>);

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
</script>
</body>
</html>