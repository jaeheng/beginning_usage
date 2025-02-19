<?php
if (!defined('EMLOG_ROOT')) {
    die('err');
}

function plugin_setting_view()
{
    $db = Database::getInstance();

    $page = Input::getIntVar('page', 1);
    $app = Input::getStrVar('app');

    $perlogs = 15;
    $where = '';
    if ($app) {
        $where = ' where type="' . $app . '"';
        $appStr = urlencode($app);
        $url = BLOG_URL . "/admin/plugin.php?plugin=beginning_usage&app={$appStr}&page=";
        $count = $db->query('select count(*) as total from ' . DB_PREFIX . 'beginning_usage' . $where)->fetch_assoc()['total'];
    } else {
        $url = BLOG_URL . '/admin/plugin.php?plugin=beginning_usage&page=';
        $count = $db->query('select count(*) as total from ' . DB_PREFIX . 'beginning_usage')->fetch_assoc()['total'];
    }

    $pagination = pagination($count, $perlogs, $page, $url, $anchor = '');

    $data = $db->query('select * from ' . DB_PREFIX . 'beginning_usage ' . $where . ' order by id desc limit ' . ($perlogs * ($page - 1)) . ', ' . $perlogs);

    $stat = $db->query("SELECT type, COUNT(*) AS count_result FROM " . DB_PREFIX . "beginning_usage GROUP BY type;");

    $lineSql = $db->query("SELECT DATE_FORMAT(FROM_UNIXTIME(created_at), '%Y-%m') AS month, type, COUNT(*) AS count_result from " . DB_PREFIX . "beginning_usage GROUP BY month, type;");

    $chart = [];

    foreach ($stat->fetch_all() as $v) {
        // 筛选掉server_detail
        if ($v[0] !== 'server_detail') {
            $chart['x'][] = $v[0];
            $chart['y'][] = (int)$v[1];
        }
    }


    $temp = [];
    foreach ($lineSql->fetch_all() as $v) {
        // 筛选掉server_detail
        if ($v[1] !== 'server_detail') {
            if (isset($temp[$v[0]])) {
                $temp[$v[0]] += (int)$v[2];
            } else {
                $temp[$v[0]] = (int)$v[2];
            }
        }
    }

    $line = ['x' => array_keys($temp), 'y' => array_values($temp)];
    ?>
    <div class="row">
        <div class="col">
            <div class="card">
                <h6 class="card-header">模版/插件使用量统计</h6>
                <div class="card-body">
                    <div id="main" style="width: 100%;height:400px;"></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <h6 class="card-header">模版/插件使用量统计(按月)</h6>
                <div class="card-body">
                    <div id="line" style="width: 100%;height:400px;"></div>
                </div>
            </div>
        </div>
    </div>


    <div class="card shadow mb-2 mt-2">
        <h6 class="card-header">模版使用情况 (共<?= $count; ?>个) <a
                    href="<?= BLOG_URL ?>/admin/plugin.php?plugin=beginning_usage">所有模版</a>
        </h6>
        <div class="card-body">
            <table class="table table-bordered" id="content">
                <tr>
                    <th style="width: 25%;">URL</th>
                    <th style="width: 25%;">博客名称</th>
                    <th style="width: 25%;">应用类型</th>
                    <th style="width: 25%;">时间</th>
                </tr>
                <?php
                while ($item = $data->fetch_assoc()):
                    ?>
                    <tr>
                        <td><a href="<?= $item['url'] ?>" target="_blank"><?= $item['url'] ?></a></td>
                        <td><?= $item['blogname'] ?></td>
                        <td>
                            <a href="<?= BLOG_URL ?>/admin/plugin.php?plugin=beginning_usage&app=<?= $item['type'] ?>"><?= $item['type'] ?></a>
                        </td>
                        <td><?= smartDate($item['created_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <div class="page">
                <?= $pagination; ?>
            </div>
        </div>
    </div>


    <?php
    // 模版销售情况
    require_once EMLOG_ROOT . '/content/plugins/beginning_usage/beginning_usage_config.php';

    $content = file_get_contents($beginning_usage_config['emlog_store_list_path']);
    $content = explode("\n", trim($content));

    $data = [];
    $product = [];
    foreach ($content as $v) {
        $v = explode("\t", $v);
        if ($v[0] === '订单号') {
            continue;
        }
        $k = mb_substr($v[6], 0, 7);
        if (isset($data[$k])) {
            $data[$k] += (float)$v[2];
        } else {
            $data[$k] = (float)$v[2];
        }
        @$product[mb_substr($v[1], 5)] += (float)$v[2];
    }
    $template_stat = [
        'x' => array_keys($data),
        'y' => array_values($data),
        'x2' => array_keys($product),
        'y2' => array_values($product)
    ]
    ?>

    <div class="row">
        <div class="col">
            <div class="card shadow mt-2">
                <h6 class="card-header">模版/插件销售统计（按月）</h6>
                <div class="card-body">
                    <div id="chart3" style="width: 100%;height:400px;"></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow mt-2">
                <h6 class="card-header">模版/插件销售统计（按应用）</h6>
                <div class="card-body">
                    <div id="chart4" style="width: 100%;height:400px;"></div>
                </div>
            </div>
        </div>
    </div>


    <script src="<?php echo BLOG_URL ?>/content/plugins/beginning_usage/echarts.min.js"></script>
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
                show: false,
                text: '模版/插件使用量统计'
            },
            tooltip: {},
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
                show: false,
                text: '模版/插件使用量统计（按月）'
            },
            tooltip: {},
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
                show: false,
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
                show: false,
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
                    data: chartData3.y2.map((v, i) => {
                        return {
                            value: v,
                            itemStyle: {
                                color: ['#333', '#fa3b25', '#133d7f', '#f3e74b', '#d5bcff', '#ccc', '#3491fa'][i] || 'black'
                            }
                        }
                    }),
                    color: 'red'
                }
            ]
        };

        // 使用刚指定的配置项和数据显示图表。
        myChart4.setOption(option4);
    </script>
<?php }