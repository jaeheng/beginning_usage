<?php
if (!defined('EMLOG_ROOT')) {
	die('err');
}

function plugin_setting_view() {
    $db = Database::getInstance();
    $page = Input::getIntVar('page', 1);
    $total = $db->query('select count(*) as total from ' . DB_PREFIX . 'beginning_usage');
    $count = $total->fetch_assoc()['total'];
    $perlogs = 15;
    $url = BLOG_URL . '/admin/plugin.php?plugin=beginning_usage&page=';
    $pagination = pagination($count, $perlogs, $page, $url, $anchor = '');

    $data = $db->query('select * from ' . DB_PREFIX . 'beginning_usage order by id desc limit ' . ($perlogs * ($page - 1)) . ', ' . $perlogs);

    $stat = $db->query("SELECT type, COUNT(*) AS count_result FROM " . DB_PREFIX . "beginning_usage GROUP BY type;");

    $lineSql = $db->query("SELECT DATE_FORMAT(FROM_UNIXTIME(created_at), '%Y-%m') AS month, type, COUNT(*) AS count_result from " . DB_PREFIX . "beginning_usage GROUP BY month, type;");

    $chart = [];

    foreach($stat->fetch_all() as $v)
    {
        $chart['x'][] = $v[0];
        $chart['y'][] = (int)$v[1];
    }

    $temp = [];
    foreach($lineSql->fetch_all() as $v)
    {
        $temp[$v[0]] += (int)$v[2];
    }

    $line = ['x' => array_keys($temp), 'y' => array_values($temp)];
	?>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">模版使用情况</h1>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <div id="main" style="width: 100%;height:400px;"></div>
                </div>
                <div class="col">
                    <div id="line" style="width: 100%;height:400px;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow mb-4 mt-2">
        <div class="card-body">
            <div class="card-title">
                <div>
                    模版使用情况 (共<?= $count;?>个)
                </div>
            </div>
            <hr/>
            <table class="table table-bordered" id="content">
                <tr>
                    <th>URL</th>
                    <th>博客名称</th>
                    <th>应用类型</th>
                    <th>时间</th>
                </tr>
                <?php
                while ($item = $data->fetch_assoc()):
                    ?>
                    <tr>
                        <td><a href="<?= $item['url']?>" target="_blank"><?= $item['url']?></a></td>
                        <td><?= $item['blogname']?></td>
                        <td><?= $item['type']?></td>
                        <td><?= smartDate($item['created_at'])?></td>
                    </tr>
                <?php endwhile;?>
            </table>

            <div class="page">
                <?= $pagination;?>
            </div>
        </div>
    </div>

    <div class="card shadow mt-2">
        <div class="card-body">
            <div class="card-title">模版销售情况</div>
            <?php
            // 模版销售情况
            $content = file_get_contents("/Users/zhangjiawei/Documents/大健康项目/emlog.txt");

            $content = explode("\n", trim($content));

            $data = [];
            $product = [];
            foreach($content as $v)
            {
                $v = explode("\t", $v);
                if ($v[0] === '订单号') {
                    continue;
                }
                $data[mb_substr($v[6], 0, 7)] += (float)$v[2];
                $product[mb_substr($v[1], 5)] += (float)$v[2];
            }
            $template_stat = [
                'x' => array_keys($data),
                'y' => array_values($data),
                'x2' => array_keys($product),
                'y2' => array_values($product)
            ]
            ?>

            <div id="chart3" style="width: 100%;height:400px;"></div>
            <div id="chart4" style="width: 100%;height:400px;"></div>
        </div>
    </div>
    <script src="<?php echo BLOG_URL?>/content/plugins/beginning_usage/echarts.min.js"></script>
    <script>
        setTimeout(hideActived, 3600);
        $("#menu_category_ext").addClass('active');
        $("#menu_ext").addClass('show');
        $("#beginning_usage").addClass('active');

        var chartData = <?php echo json_encode($chart);?>;

        // 基于准备好的dom，初始化echarts实例
        var myChart = echarts.init(document.getElementById('main'));

        // 指定图表的配置项和数据
        var option = {
            title: {
                text: '模版/插件使用量统计'
            },
            tooltip: {},
            legend: {
                data: ['使用量']
            },
            xAxis: {
                data: chartData.x,
                axisLabel: {
                    hideOverlap: false,
                    rotate: -45
                }
            },
            yAxis: {},
            series: [
                {
                    name: '使用量',
                    type: 'bar',
                    data: chartData.y
                }
            ]
        };

        // 使用刚指定的配置项和数据显示图表。
        myChart.setOption(option);


        // 基于准备好的dom，初始化echarts实例
        var myChart2 = echarts.init(document.getElementById('line'));
        var chartData2 = <?php echo json_encode($line);?>;

        // 指定图表的配置项和数据
        var option2 = {
            title: {
                text: '模版/插件使用量统计（按月）'
            },
            tooltip: {},
            legend: {
                data: ['使用量']
            },
            xAxis: {
                data: chartData2.x,
                axisLabel: {
                    hideOverlap: false,
                    rotate: -45
                }
            },
            yAxis: {},
            series: [
                {
                    name: '使用量',
                    type: 'bar',
                    data: chartData2.y
                }
            ]
        };

        // 使用刚指定的配置项和数据显示图表。
        myChart2.setOption(option2);


        // 基于准备好的dom，初始化echarts实例
        var myChart3 = echarts.init(document.getElementById('chart3'));
        var chartData3 = <?php echo json_encode($template_stat);?>;

        // 指定图表的配置项和数据
        var option3 = {
            title: {
                text: '模版/插件销售统计（按月）'
            },
            tooltip: {},
            xAxis: {
                data: chartData3.x,
                axisLabel: {
                    hideOverlap: false,
                    rotate: -45
                }
            },
            yAxis: {},
            series: [
                {
                    name: '销售量(元)',
                    type: 'bar',
                    data: chartData3.y
                }
            ]
        };

        // 使用刚指定的配置项和数据显示图表。
        myChart3.setOption(option3);


        // 基于准备好的dom，初始化echarts实例
        var myChart4 = echarts.init(document.getElementById('chart4'));

        // 指定图表的配置项和数据
        var option4 = {
            title: {
                text: '模版/插件销售统计（按产品）'
            },
            tooltip: {},
            xAxis: {
                data: chartData3.x2,
                axisLabel: {
                    hideOverlap: false,
                    rotate: -45
                }
            },
            yAxis: {},
            series: [
                {
                    name: '销售量(元)',
                    type: 'bar',
                    data: chartData3.y2
                }
            ]
        };

        // 使用刚指定的配置项和数据显示图表。
        myChart4.setOption(option4);
    </script>
<?php }