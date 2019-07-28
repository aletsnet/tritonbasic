$("#"+idefectivo).change(function(obj){
    var a = $("#"+idefectivo).val();
    var b = $("#"+idtotal).val();
    var suma = parseFloat(a) - parseFloat(b);
    $("#"+iddiferencia).html("$ " + suma.toFixed(2));
});