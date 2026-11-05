<?php

// Conta os livros lidos em cada um dos últimos 6 meses, pela data em que foram acabados (data_fim)
function obterLeiturasPorMes($conn, $userId) {
    $nomesMeses = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"];

    // Os 6 meses do gráfico, do mais antigo ao atual, todos começam com 0 livros
    $meses = [];
    for ($i = 5; $i >= 0; $i--) {
        $data = strtotime("first day of -$i month");
        $meses[date("Y-m", $data)] = [
            'nome' => $nomesMeses[date("n", $data) - 1],
            'total' => 0
        ];
    }

    // Primeiro dia do mês mais antigo, ex.: "2026-06-01"
    $inicio = array_key_first($meses) . "-01";

    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(data_fim, '%Y-%m') AS mes, COUNT(*) AS total
        FROM biblioteca
        WHERE utilizador_id = ? AND estado = 'lido' AND data_fim >= ?
        GROUP BY mes
    ");
    $stmt->bind_param("is", $userId, $inicio);
    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($linha = $resultado->fetch_assoc()) {
        // Só guarda os meses que aparecem no gráfico (ignora, por exemplo, uma data no futuro)
        if (isset($meses[$linha['mes']])) {
            $meses[$linha['mes']]['total'] = $linha['total'];
        }
    }
    return $meses;
}

// Diz se o utilizador já marcou algum livro como lido (em qualquer data)
function temLivrosLidos($conn, $userId) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM biblioteca WHERE utilizador_id = ? AND estado = 'lido'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'] > 0;
}
