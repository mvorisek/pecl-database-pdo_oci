--TEST--
No refcount leak when object is used for bind
--EXTENSIONS--
pdo
pdo_oci
--SKIPIF--
<?php
require(getenv('PDO_TEST_DIR').'/pdo_test.inc');
PDOTest::skip();
?>
--FILE--
<?php

require(getenv('PDO_TEST_DIR').'/pdo_test.inc');
$db = PDOTest::factory();

$bindTypes = [
    PDO::PARAM_BOOL,
    PDO::PARAM_NULL,
    PDO::PARAM_INT,
    PDO::PARAM_STR,
    PDO::PARAM_STR_NATL,
    PDO::PARAM_STR_CHAR,
    // PDO::PARAM_LOB,
];

$sql = 'SELECT ' . implode(', ', array_map(fn ($v) => ':' . $v, range('a', chr(ord('a') + count($bindTypes) * 2 - 1)))) . ' FROM dual';
$stmt = $db->prepare($sql);

$vars = [];
foreach ($bindTypes as $i => $bindType) {
    $vars[$i] = new class() extends stdClass {
        public function __toString(): string
        {
            return '10';
        }
    };
    $stmt->bindValue(':' . chr(ord('a') + $i * 2), $vars[$i], $bindType);
    $stmt->bindParam(':' . chr(ord('a') + $i * 2 + 1), $vars[$i], $bindType);
}

$stmt->execute();

$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($res);

?>
--EXPECTF--
Array
(
    [0] => Array
        (
            [:a] => 1
            [:b] => 1
            [:c] => 10
            [:d] => 10
            [:e] => 10
            [:f] => 10
            [:g] => 10
            [:h] => 10
            [:i] => 10
            [:j] => 10
            [:k] => 10
            [:l] => 10
        )

)
