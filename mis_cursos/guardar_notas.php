<?php
session_start();
require_once "../includes/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_curso'])) {
    $id_curso = $_POST['id_curso'];
    $notas = $_POST['notas'] ?? [];

    if (!empty($notas)) {
        $sql = "UPDATE matriculas SET calificacion = ? WHERE id_matricula = ?";
        $stmt = $mysqli->prepare($sql);

        foreach ($notas as $id_matricula => $nota) {
            // Validar nota (puede ser null/vacía)
            $calificacion = ($nota === '') ? null : $nota;
            $stmt->bind_param("di", $calificacion, $id_matricula); // d for double/decimal
            $stmt->execute();
        }
        $stmt->close();
    }

    // Redirigir con mensaje de éxito (usando parámetro GET que podría leer SweetAlert en ver_curso.php si lo implementamos)
    header("Location: ver_curso.php?id=$id_curso&msg=saved");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>