# Como contribuir

Este guia define como o trabalho é organizado no Koobli: issues, branches, commits e pull requests.

## Fluxo de trabalho

1. **Escolher uma issue.** Todo o trabalho começa numa issue. Se não existir, cria-a primeiro.
2. **Criar uma branch** a partir da `main` atualizada:
   ```bash
   git switch main
   git pull
   git switch -c fix/1-query-favoritos
   ```
3. **Fazer commits pequenos** e com mensagens claras (ver abaixo).
4. **Abrir uma pull request** para a `main`. Na descrição, escreve `Closes #1` para que a issue feche automaticamente com o merge.
5. **Fazer merge e apagar a branch**, tanto no GitHub como localmente:
   ```bash
   git switch main
   git pull
   git branch -d fix/1-query-favoritos
   ```

Nunca faças commit diretamente na `main`.

## Nomes das branches

Formato: `tipo/numero-da-issue-descricao-curta`

| Tipo       | Quando usar                                    | Exemplo                        |
|------------|------------------------------------------------|--------------------------------|
| `feat`     | Nova funcionalidade                            | `feat/20-salas-de-foco`        |
| `fix`      | Correção de bug                                | `fix/1-query-favoritos`        |
| `refactor` | Reorganizar código sem mudar o comportamento   | `refactor/3-sidebar-include`   |
| `perf`     | Melhorar o desempenho                          | `perf/6-cache-carrossel`       |
| `style`    | Apenas CSS ou visual                           | `style/23-layout-responsivo`   |
| `docs`     | Documentação                                   | `docs/25-readme-final`     |
| `chore`    | Configuração, dependências, tarefas de manutenção | `chore/2-atualizar-gitignore`    |

## Mensagens de commit

Formato ([Conventional Commits](https://www.conventionalcommits.org/pt-br/)):

```
tipo: descrição curta no imperativo
```

- Usa os mesmos tipos das branches.
- Escreve no imperativo: "adiciona", "corrige", "remove" (e não "adicionei" ou "adicionado").
- A primeira linha deve ter no máximo ~70 caracteres.
- Se precisares de explicar o *porquê*, deixa uma linha em branco e escreve o corpo da mensagem.

Exemplos:

```
fix: corrige query da página de favoritos
feat: adiciona temporizador às salas de foco
refactor: move sidebar para includes/sidebar.php
docs: atualiza instruções de instalação
```

Cada commit deve fazer **uma coisa**. Se a mensagem precisar de um "e", provavelmente devia ser dividido em dois commits.

## Pull requests

- O título segue o formato dos commits: `fix: corrige query da página de favoritos`.
- A descrição explica em poucas linhas o que mudou e como testar, e inclui `Closes #N`.
- Antes do merge, testa localmente as páginas afetadas.
