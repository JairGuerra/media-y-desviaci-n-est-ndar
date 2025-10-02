<?php
session_start();

if (!isset($_SESSION['numeros'])) {
    $_SESSION['numeros'] = [];
}
$resultado = null;
$error = null;

function calcularMedia($nums)
{
    $n = count($nums);
    return $n > 0 ? array_sum($nums) / $n : 0;
}
function calcularDesviacionEstandar($nums)
{
    $n = count($nums);
    if ($n === 0)
        return 0;
    $media = calcularMedia($nums);
    $suma = 0;
    foreach ($nums as $v)
        $suma += pow($v - $media, 2);
    return sqrt($suma / $n);
}

// --- Manejo de formularios ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['agregar'])) {
        $raw = trim($_POST['numero'] ?? '');
        if ($raw === '') {
            $error = "Ingresa uno o varios números.";
        } else {
            $partes = explode(",", $raw);
            foreach ($partes as $p) {
                $p = trim($p);
                if ($p !== '' && is_numeric($p)) {
                    $_SESSION['numeros'][] = floatval($p);
                }
            }
        }
    } elseif (isset($_POST['calcular'])) {
        if (empty($_SESSION['numeros'])) {
            $error = "No hay números para calcular.";
        } else {
            $media = calcularMedia($_SESSION['numeros']);
            $desv = calcularDesviacionEstandar($_SESSION['numeros']);
            $resultado = [
                'media' => round($media, 2),
                'desviacion' => round($desv, 2)
            ];
        }
    } elseif (isset($_POST['reset'])) {
        $_SESSION['numeros'] = [];
    } elseif (isset($_POST['eliminar'])) {
        $i = intval($_POST['indice']);
        if (isset($_SESSION['numeros'][$i])) {
            unset($_SESSION['numeros'][$i]);
            $_SESSION['numeros'] = array_values($_SESSION['numeros']); // reindexar
        }
    }
}

// --- Paginación ---
$por_pagina = 10; 
$total = count($_SESSION['numeros']);
$paginas = ceil($total / $por_pagina);
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$inicio = ($pagina_actual - 1) * $por_pagina;
$numeros_pagina = array_slice($_SESSION['numeros'], $inicio, $por_pagina);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calculadora de Media y Desviación Estándar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Calculadora de Media y Desviación Estándar</h1>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Ingresar números (separados por coma):</label>
        <input type="text" name="numero" placeholder="Ej: 5, 8, 12.3">
        <button type="submit" name="agregar">Agregar</button>
        <button type="submit" name="calcular">Calcular</button>
        <button type="submit" name="reset">Reiniciar</button>
    </form>

    <?php if (!empty($_SESSION['numeros'])): ?>
        <h2>Números guardados:</h2>
        <table>
            <tr>
                <th>#</th>
                <th>Valor</th>
                <th>Acción</th>
            </tr>
            <?php foreach ($numeros_pagina as $i => $num): ?>
                <tr>
                    <td><?= $inicio + $i + 1 ?></td>
                    <td><?= htmlspecialchars($num) ?></td>
                    <td>
                        <form method="post" class="inline">
                            <input type="hidden" name="indice" value="<?= $inicio + $i ?>">
                            <button type="submit" name="eliminar">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Navegación de páginas -->
        <div class="paginacion">
            <?php for ($p = 1; $p <= $paginas; $p++): ?>
                <?php if ($p == $pagina_actual): ?>
                    <strong>[<?= $p ?>]</strong>
                <?php else: ?>
                    <a href="?pagina=<?= $p ?>"><?= $p ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($resultado)): ?>
        <div class="resultado">
            <p><strong>Media:</strong> <?= $resultado['media'] ?></p>
            <p><strong>Desviación Estándar:</strong> <?= $resultado['desviacion'] ?></p>
        </div>
    <?php endif; ?>
</body>
</html>
