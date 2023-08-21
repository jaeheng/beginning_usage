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

    $data = $db->query('select * from ' . DB_PREFIX . 'beginning_usage limit ' . ($perlogs * ($page - 1)) . ', ' . $perlogs);

	?>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">模版使用情况</h1>
    </div>
    <div class="card shadow mb-4 mt-2">
        <div class="card-body">
            <div class="card-title">
                <div>
                    模版使用情况
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
                        <td><?= $item['url']?></td>
                        <td><?= $item['blogname']?></td>
                        <td><?= $item['type']?></td>
                        <td><?= smartDate($item['created_at'])?></td>
                    </tr>
                <?php endwhile;?>
            </table>

            <?= $pagination;?>
        </div>
    </div>
    <script>
        setTimeout(hideActived, 3600);
        $("#menu_category_ext").addClass('active');
        $("#menu_ext").addClass('show');
        $("#beginning_usage").addClass('active');
    </script>
<?php }