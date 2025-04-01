<div id="mare" class="h-screen w-full"></div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const map = L.map('mare').setView([-22.856, -43.247], 32);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            const logoControl = L.Control.extend({
                options: {
                    position: 'bottomleft'
                },
                onAdd: function() {
                    const container = L.DomUtil.create('div', 'leaflet-control leaflet-bar');
                    const img = L.DomUtil.create('img', '', container);

                    img.src = @json(asset('images/logo.png'));
                    img.style.width = '240px';
                    img.style.background = 'white';
                    img.style.padding = '8px';
                    img.style.borderRadius = '4px';

                    return container;
                }
            });
            
            map.addControl(new logoControl());

            const data = @json($data);

            data.forEach(point => {
                const marker = L.marker([point.lat, point.lng]).addTo(map);

                let content =`<div class="flex flex-col leading-tight">`;

                if (point.url) {
                    content += `<img src="${point.url}" alt="${point.name}" class="w-full max-h-80 mb-4 mt-4" />`;
                }

                content += `<strong class="text-base font-semibold block mb-2 leading-tight">${point.name}</strong>`;

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
        });
    </script>
@endpush
