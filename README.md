# mod_qrcodecheck - Registro por QR Code

Atividade Moodle para registrar presença ou participação usando um QR Code temporário projetado pelo professor.

## Funcionamento

- O QR muda a cada **10 segundos**.
- Cada QR é aceito por no máximo **20 segundos** a partir da sua geração.
- O link do QR chega primeiro em `scan.php` e o clique válido é persistido **antes de `require_login()`**.
- Se o aluno ainda não estiver autenticado, recebe uma continuação temporária de uso único. Assim, pode concluir o login
  mesmo depois de o QR original expirar.
- A continuação do login expira em 15 minutos e fica vinculada à mesma sessão anônima do navegador que leu o QR.
- Cada projeção é uma sessão separada.
- O IP do computador do professor/projetor é salvo no início da sessão.
- O relatório compara o IP observado no clique do aluno com o IP do professor/projetor e destaca quem está em IP
  diferente.
- Apenas o primeiro registro confirmado de cada aluno em cada sessão é mantido.
- Leituras pendentes antigas são removidas automaticamente por tarefa agendada.

## Privacidade

O plugin armazena horário da leitura, IP do aluno, IP de referência da sessão. Esses dados são declarados na Privacy API
do Moodle.

## Requisitos

Moodle 4.5 ou superior.

## Licença

GPL v3 ou posterior.
