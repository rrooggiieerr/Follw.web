class Follw {
	map = null;
	marker = null;
	accuracy = null;
	textOverlay = null;
	onLocationChangeHooks = [];
	onIDDeletedHooks = [];
	
	constructor(element, followURL, zoom = 12) {
		this.element = element;
		this.followURL = followURL + ".json";
		this.zoom = zoom

		// See if DOM is already available
		if (document.readyState === 'complete' || document.readyState === 'interactive') {
			// call on next available tick
			var _this = this;
			setTimeout(function() {
				_this.init();
			}, 1);
		} else {
			var _this = this;
			document.addEventListener('DOMContentLoaded', function() {
				_this.init();
			});
		}
	}

	init() {
		//this.map = L.map(this.element).fitWorld();
		this.map = L.map(this.element);
		L.control.scale().addTo(this.map);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>',
			maxZoom: 19
		}).addTo(this.map);
		
		var _this = this;
		setTimeout(function() {
			_this.getLocation();
		}, 1000);
	}

	setLocation(location, accuracy) {
		if(location == null) {
			//_this.map.setView([0, 0], 2);
			this.map.fitWorld();
			this.map.zoomControl.disable();
			this.map.dragging.disable();
			this.map.touchZoom.disable();
			this.map.doubleClickZoom.disable();
			this.map.scrollWheelZoom.disable();
	
			if(this.marker != null) {
				this.map.removeLayer(this.marker);
				this.marker = null;
			}
			
			if(this.accuracy != null) {
				this.map.removeLayer(this.accuracy);
				this.accuracy = null;
			}
			
			if(this.textOverlay == null) {
				var leafletContainer = document.getElementById(this.element);
				this.textOverlay = document.createElement('div');
				this.textOverlay.id = leafletContainer.id + '_textoverlay';
				this.textOverlay.style.zIndex = 500;
				this.textOverlay.style.position = 'relative';
				this.textOverlay.style.textAlign = 'center';
				this.textOverlay.style.top = '50%';
				this.textOverlay.style.transform = 'translate(0%, -50%)';
				leafletContainer.appendChild(this.textOverlay);
			}
			this.textOverlay.innerHTML = "No location is currently being shared";
		} else {
			this.map.zoomControl.enable();;
			this.map.dragging.enable();
			this.map.touchZoom.enable();
			this.map.doubleClickZoom.enable();
			this.map.scrollWheelZoom.enable();
	
			if(this.marker == null) {
				this.map.setView(location, this.zoom);
				this.marker = L.circleMarker(location, {radius: 4, stroke: false, fillOpacity: 1}).addTo(this.map);
			} else {
				this.map.setView(location);
				this.marker.setLatLng(location);
			}
	
			if(accuracy != null) {
				if(this.accuracy == null) {
					this.accuracy = L.circle(location, {radius: accuracy, weight: 1}).addTo(this.map);
				} else {
					this.accuracy.setLatLng(location);
					this.accuracy.setRadius(accuracy);
				}
			} else if(this.accuracy != null) {
				// Remove the accuracy circle
				this.map.removeLayer(this.accuracy);
				this.accuracy = null;
			}

			if(this.textOverlay != null) {
				this.textOverlay.innerHTML = "";
			}
		}
	}

	getLocation() {
		var request = new XMLHttpRequest();
		request.open('GET', this.followURL, true);
		var _this = this;
		request.onload = function() {
			if (this.status == 200) {
				var data = JSON.parse(this.response);

				if(typeof _this.lastTimestamp == "undefined" || _this.lastTimestamp == null || _this.lastTimestamp != data.timestamp) {
					_this.lastTimestamp = data.timestamp;

					_this.setLocation([data.latitude, data.longitude], data.accuracy);

					_this.onLocationChangeHooks.forEach(function(hook) {
						hook(data);
					});
				}

				setTimeout(function() {
					_this.getLocation();
				}, data.interval * 1000);
			} else if (this.status == 410) {
				// Deleted
				_this.onIDDeletedHooks.forEach(function(hook) {
					hook();
				});
			} else {
				if(typeof _this.lastTimestamp == "undefined" || _this.lastTimestamp != null) {
					_this.lastTimestamp = null;
	
					_this.setLocation(null, null);
					
					_this.onLocationChangeHooks.forEach(function(hook) {
						hook(null);
					});
				}

				setTimeout(function() {
					_this.getLocation();
				}, 1000);
			}
		};

		request.send();
	}
	
	onLocationChange(hook) {
		this.onLocationChangeHooks.push(hook);
	}
	
	onIDDeleted(hook) {
		this.onIDDeletedHooks.push(hook);
	}
	
	invalidateSize() {
		this.map.invalidateSize();
	}
}