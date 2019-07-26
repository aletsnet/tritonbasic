var points = maplocal.split(",");
var lmark = {lat: parseFloat(points[0]), lng: parseFloat(points[1])};
function initMap() {
    console.log("Show map: "+ maplocal);
    map = new google.maps.Map(document.getElementById('map'), {
        center: lmark,
        zoom: 18
    });
    
    map.addListener('dblclick', function(event) {
        var lat = event.latLng.lat();
        var lng = event.latLng.lng();
        
        document.getElementById(name).value = lat + "," + lng;
        //map.setCenter(new google.maps.LatLng(latlng[0],latlng[1]));
        var marker = new google.maps.Marker({position:new google.maps.LatLng(lat,lng), icon: iconmarck });
        marker.setMap(map);
    });
    
    function codeAddress() {
        var address = document.getElementById(txtaddres).value;
        var geocoder  = new google.maps.Geocoder();
        if(address != ""){
            geocoder.geocode( { 'address': "" + address}, function(results, status) {
                if (status == 'OK') {
                    map.setCenter(results[0].geometry.location);
                    map.setCenter(results[0].geometry.location);
                    var marker = new google.maps.Marker({ position: results[0].geometry.location , icon: iconmarck});
                    marker.setMap(map);
                }else{
                    $("#lubicacion").html("Imposible determinar la ubicacion");
                }
            });
        }else{
            $("#lubicacion").html("Se necesita una dirección");
        }
	}
    
    var iconmarck = {
        url: 'image/ping_map.png',
        scaledSize: new google.maps.Size(48, 48),
        origin: new google.maps.Point(0, 0)
    };
    
    function marckMake(){
        var lpoints = mapmarks.split(",");
        var llmark = {lat: parseFloat(lpoints[0]), lng: parseFloat(lpoints[1])};
        map.setCenter(llmark);
        var marker = new google.maps.Marker({
            position: llmark,
            icon: iconmarck
        });
        
        // To add the marker to the map, call setMap();
        marker.setMap(map);
    }
    
    $("#btnMapMarck").click(function () {
        marckMake();
        console.log("Show map: "+ mapmarks);
    });
    
    $("#btnMapSearch").click(function () {
        console.log("Search map ");
        codeAddress();
    });
    
    var marker = new google.maps.Marker({position: lmark, map: map, icon: iconmarck});
    
    
}