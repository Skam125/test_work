<?php

$dsn = 'mysql:dbname=test_work;host=localhost';
$user = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $user, $password);
    echo '<pre>';
    if (isset($_GET['ident'])) {
        print_r(unserialize(versionControl($pdo, $_GET)));
    }
    echo '</pre>';
} catch (PDOException $e) {
    echo 'Подключение не удалось: ' . $e->getMessage();
}
$pdo = null;


/**
 * @param PDO $pdo
 * @param array $input
 * @return string
 */
function versionControl(PDO $pdo, array $input)
{
    $result['delete'] = array();
    $result['update'] = array();
    $result['new'] = array();

    $in_statement = '\'' . implode("','", $input['ident']) . '\'';

    $sql = "SELECT * FROM data WHERE ident IN ({$in_statement})";
    $db_data = $pdo->query($sql);

    $sql = "SELECT * FROM data WHERE ident NOT IN ({$in_statement})";
    $db_new_data = $pdo->query($sql);
    // $result['update']
    while ($row = $db_data->fetch(PDO::FETCH_ASSOC)) {
        foreach ($input['ident'] as $key => $value) {
            if ($row['ident'] == $value && $row['version'] > $input['version'][$key]) {

                $result['update'][$row['ident']] = array(
                    'value' => $row['value'],
                    'version' => $row['version'],
                );

                unset($input['ident'][$key]);

            } elseif ($row['ident'] == $value) {
                unset($input['ident'][$key]);
            }
        }
    }
    // $result['new']
    while ($row = $db_new_data->fetch(PDO::FETCH_ASSOC)) {
        $result['new'][$row['ident']] = array(
            'value' => $row['value'],
            'version' => $row['version'],
        );
    }
    // $result['delete']
    if ($input['ident']) {
        foreach ($input['ident'] as $value) {
            $result['delete'] = array(
                $value
            );
        }
    }

    return serialize($result);
}


