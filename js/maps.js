function initialize() {
	var hlat = parseFloat(helper.lat)||39.0000;
	var hlng = parseFloat(helper.lng)||22.0000;

	var myLatLng = new google.maps.LatLng(hlat,hlng);
	
	var mapOptions = {
	  center: myLatLng,
	  zoom: 5
	};
	var map = new google.maps.Map(document.getElementById('map-canvas'),
	    mapOptions);
	var marker = new google.maps.Marker({position: myLatLng, map: map, draggable: true});
	marker.setMap(map);

  google.maps.event.addListener(marker, 'dragend', function(event) {
        placeMarker(event.latLng);
    });

 // Create the search box and link it to the UI element.
        var input = document.getElementById('pac-input');
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
          searchBox.setBounds(map.getBounds());
        });

	function placeMarker(location) {



	    if (marker == undefined){
	        marker = new google.maps.Marker({
	            position: location,
	            map: map,
	            animation: google.maps.Animation.DROP
	        });
	    }
	    else {
	        marker.setPosition(location);
	    }
	    map.setCenter(location);
	    //console.log(location.lat()+" "+location.lng());		// click debug
	    document.getElementById("latitude").value = location.lat();
	    document.getElementById("longitude").value = location.lng();
	}

}
google.maps.event.addDomListener(window, 'load', initialize);