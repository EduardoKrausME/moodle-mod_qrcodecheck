# mod_qrcodecheck - Registro por QR Code

Atividade Moodle para registrar presença ou participação usando um QR Code temporário projetado pelo professor.

## Como funciona

- o QR muda a cada **10 segundos**;
- cada QR é aceito por no máximo **20 segundos** a partir da geração;
- o link chega primeiro em `scan.php` e uma leitura válida é persistida antes da autenticação;
- se o aluno ainda não estiver autenticado, recebe uma continuação temporária de uso único para concluir o login mesmo
  depois de o QR original expirar;
- a continuação do login expira em 15 minutos e fica vinculada à mesma sessão anônima do navegador que leu o QR;
- cada projeção é tratada como uma sessão separada;
- o IP do computador do professor ou projetor é salvo no início da sessão;
- o relatório compara o IP observado no clique do aluno com o IP de referência e destaca registros feitos em IP
  diferente;
- apenas o primeiro registro confirmado de cada aluno em cada sessão é mantido;
- leituras pendentes antigas são removidas automaticamente por tarefa agendada.

## Privacidade

O plugin armazena o horário da leitura, o IP do aluno e o IP de referência da sessão. Esses dados são declarados na
Privacy API do Moodle.
