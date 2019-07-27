function addList(obj, destino){
    var slits = $("#"+destino).val();
    var list = slits.split(",");
    var newlist = "";
    if(obj.checked){
        newlist += obj.value;
        for(row in list){
            l = list[row];
            if(l != obj.value && l != ""){
                if(newlist != ""){ newlist += ","; }
                newlist += l;
            }
        }
    }else{
        for(row in list){
            l = list[row];
            if(!(l == obj.value)){
                if(newlist != ""){ newlist += ","; }
                newlist += l;
            }
        }
    }
    $("#" + destino).val(newlist);
    //console.log(obj);
}