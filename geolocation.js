function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError);
    } else {
        console.log('Geolocation is not supported by this browser.')
    }
}

function showPosition(position) {
    fetch('https://api.openweathermap.org/geo/1.0/reverse?'
        + 'lat=' + position.coords.latitude
        + '&lon=' + position.coords.longitude
        + '&appid=685a5f6c0ec7432361a364184373d9cd')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data && data.length > 0) {
                const city = data[0].name;
                Swal.fire({
                    title: 'Is your city ' + city + '?',
                    showDenyButton: true,
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    denyButtonText: `No`,
                    confirmButtonColor: '#3085d6',
                    denyButtonColor: '#2c7dc6',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire("Your location has been determined!", "", "success");
                    } else if (result.isDenied) {
                        Swal.fire("Your location has not been determined!", "", "info");
                    }
                });
            } else {
                Swal.fire("Location not found!", "", "info");
            }
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
            Swal.fire("Error fetching location data!", "", "error");
        });
}

function showError(error) {
    switch (error.code) {
        case error.PERMISSION_DENIED:
            console.log('User denied the request for Geolocation.')
            break;
        case error.POSITION_UNAVAILABLE:
            console.log('Location information is unavailable.')
            break;
        case error.TIMEOUT:
            console.log('The request to get user location timed out.')
            break;
        case error.UNKNOWN_ERROR:
            console.log('An unknown error occurred.')
            break;
    }
}

getLocation();