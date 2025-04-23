<div class="flex h-screen w-full">
    <div class="hidden md:block w-[400px] bg-white shadow-lg overflow-y-auto">
        <div class="p-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-32 mt-4 mb-8">
            <div class="mb-4">
                <input type="text" placeholder="Buscar locais..."
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="space-y-2">
                <div class="flex items-center">
                    <label for="accessible">
                        <input type="checkbox" id="accessible" class="w-4 h-4 mr-2">
                        <span>Acessível</span>
                    </label>
                </div>
                <div class="flex items-center">
                    <label for="partial_accessible">
                        <input type="checkbox" id="partial_accessible" class="w-4 h-4 mr-2">
                        <span>Parcialmente Acessível</span>
                    </label>
                </div>
            </div>
            <div class="mt-6">
                <h3 class="text-lg font-medium mb-2">Resultados</h3>
                <div id="results-list" class="space-y-2"></div>
            </div>
        </div>
    </div>

    <div id="mare" class="flex-1 h-full"></div>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const map = L.map('mare').setView([-22.856, -43.247], 32);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            const data = @json($data);
            const markers = [];
            const resultsList = document.getElementById('results-list');

            function updateResultsList(filteredData) {
                resultsList.innerHTML = '';
                filteredData.forEach(point => {
                    const item = document.createElement('div');
                    item.className =
                        'p-3 border rounded-lg cursor-pointer hover:bg-gray-50 flex items-center justify-between group';

                    const iconType = point.accessible ? 'text-blue-500' : (point.partial_accessible ?
                        'text-green-500' : 'text-red-500');
                    const iconClass = point.accessible ? 'heroicon-o-check-circle' : (point.partial_accessible ?
                        'heroicon-o-exclamation-circle' : 'heroicon-o-x-circle');

                    item.innerHTML = `
                        <div class="flex items-center">
                            <i class="${iconClass} ${iconType} mr-2 text-lg"></i>
                            <div>
                                <h4 class="font-medium">${point.name}</h4>
                                <p class="text-sm text-gray-600">${point.type}</p>
                            </div>
                        </div>
                        <i class="heroicon-o-arrow-right opacity-0 group-hover:opacity-100 transform group-hover:translate-x-1 transition-all duration-200"></i>
                    `;
                    item.addEventListener('click', () => {
                        map.setView([point.lat, point.lng], 18);
                        markers.forEach(m => m.closePopup());
                        markers.find(m => m.getLatLng().equals([point.lat, point.lng])).openPopup();
                    });
                    resultsList.appendChild(item);
                });
            }

            data.forEach(point => {
                const markerIcon = L.icon({
                    iconUrl: point.partial_accessible ?
                        'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png' :
                        (point.accessible ?
                            'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png' :
                            'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png'
                            ),
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -40],
                    shadowSize: [41, 41]
                });

                const marker = L.marker([point.lat, point.lng], {
                    icon: markerIcon
                }).addTo(map);
                markers.push(marker);

                let content = `<div class="flex flex-col leading-tight">`;

                if (point.url) {
                    content +=
                        `<img src="${point.url}" alt="${point.name}" class="w-full max-h-80 mb-4 mt-4" />`;
                }

                content +=
                    `<strong class="text-base font-semibold block mb-2 leading-tight">${point.name}</strong>`;

                content += `
                    <ul>
                        <li>Tipo: ${point.type}</li>
                        <li>Acessível: ${point.accessible ? 'Sim' : 'Não'}</li>
                        <li>Características: ${point.accessibility_features.join(', ')}</li>
                    </ul>
                `;

                if (!point.surroundings_accessible) {
                    content += `<p>Problemas: ${point.surroundings_issues}</p>`;
                }

                content += `</div>`;

                marker.bindPopup(content);
            });

            updateResultsList(data);

            const searchInput = document.querySelector('input[type="text"]');
            const accessibleCheckbox = document.getElementById('accessible');
            const partialAccessibleCheckbox = document.getElementById('partial_accessible');

            function filterResults() {
                const searchTerm = searchInput.value.toLowerCase();
                const showAccessible = accessibleCheckbox.checked;
                const showPartialAccessible = partialAccessibleCheckbox.checked;

                const filteredData = data.filter(point => {
                    const matchesSearch = point.name.toLowerCase().includes(searchTerm) ||
                        point.type.toLowerCase().includes(searchTerm);

                    const matchesAccessibility = (!showAccessible && !showPartialAccessible) ||
                        (showAccessible && point.accessible) ||
                        (showPartialAccessible && point.partial_accessible);

                    return matchesSearch && matchesAccessibility;
                });

                markers.forEach((marker, index) => {
                    if (filteredData.includes(data[index])) {
                        marker.addTo(map);
                    } else {
                        marker.remove();
                    }
                });

                updateResultsList(filteredData);
            }

            searchInput.addEventListener('input', filterResults);
            accessibleCheckbox.addEventListener('change', filterResults);
            partialAccessibleCheckbox.addEventListener('change', filterResults);
        });
    </script>
@endpush
