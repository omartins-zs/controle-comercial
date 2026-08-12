$(document).ready(function ($) {

    // ----------------------------------------------------------------
    // DataTable — tradução PT-BR + configurações padrão
    // ----------------------------------------------------------------
    if ($('#datatable').length) {
        $('#datatable').DataTable({
            language: {
                decimal:        ',',
                thousands:      '.',
                emptyTable:     'Nenhum dado disponível na tabela',
                info:           'Exibindo _START_ a _END_ de _TOTAL_ registros',
                infoEmpty:      'Exibindo 0 a 0 de 0 registros',
                infoFiltered:   '(filtrado de _MAX_ registros no total)',
                infoPostFix:    '',
                lengthMenu:     'Mostrar _MENU_ registros por página',
                loadingRecords: 'Carregando...',
                processing:     'Processando...',
                search:         'Pesquisar:',
                zeroRecords:    'Nenhum registro encontrado',
                paginate: {
                    first:    'Primeiro',
                    last:     'Último',
                    next:     'Próximo',
                    previous: 'Anterior'
                },
                aria: {
                    sortAscending:  ': ordenar coluna em ordem crescente',
                    sortDescending: ': ordenar coluna em ordem decrescente'
                }
            },
            order:      [[0, 'asc']],
            lengthMenu: [[20, 50, 100, -1], [20, 50, 100, 'Todos']],
            pageLength: 20
        });
    }

    // ----------------------------------------------------------------
    // Sidebar — desktop: colapsar/expandir (html.sidebar-recolhida)
    // ----------------------------------------------------------------
    $('#btn-sidebar-toggle').on('click', function () {
        var recolhida = document.documentElement.classList.toggle('sidebar-recolhida');
        localStorage.setItem('sidebarRecolhida', recolhida ? '1' : '0');
    });

    // ----------------------------------------------------------------
    // Sidebar — mobile: abrir/fechar (body.sidebar-aberta)
    // Fechar ao clicar no backdrop escuro.
    // ----------------------------------------------------------------
    $('#btn-sidebar-mobile').on('click', function () {
        document.body.classList.toggle('sidebar-aberta');
    });

    $('#sidebar-backdrop').on('click', function () {
        document.body.classList.remove('sidebar-aberta');
    });

    // Fecha a sidebar mobile ao clicar num link do menu
    $('#sidebar .nav-link:not([data-bs-toggle])').on('click', function () {
        document.body.classList.remove('sidebar-aberta');
    });

    // ----------------------------------------------------------------
    // Máscaras de input (jquery-mask)
    // ----------------------------------------------------------------
    $('input[name=data_nascimento]').mask('99/99/9999');
    $('input[name=telefone]').mask('(99) 99999-9999');
    $('input[name=cep]').mask('99999-999');

    // ----------------------------------------------------------------
    // Cadastro de clientes: PF / PJ
    // ----------------------------------------------------------------
    var $pf = $('#pf'), $pj = $('#pj');

    if ($pf.is(':checked')) {
        $('.pj').hide(); $('.pf').show();
        $('input[name=cpf_cnpj]').mask('999.999.999-99');
    }
    if ($pj.is(':checked')) {
        $('.pf').hide(); $('.pj').show();
        $('input[name=cpf_cnpj]').mask('99.999.999/9999-99');
    }

    $pf.on('click', function () {
        $('.pj').hide(); $('.pf').show();
        $('input[name=cpf_cnpj]').mask('999.999.999-99');
    });

    $pj.on('click', function () {
        $('.pf').hide(); $('.pj').show();
        $('input[name=cpf_cnpj]').mask('99.999.999/9999-99');
    });

});
