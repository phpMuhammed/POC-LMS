<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Video Player Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">{{ $record?->title ?? 'Video' }}</h2>
            <div class="aspect-video bg-black rounded-lg overflow-hidden">
                <video
                    id="video-player"
                    class="w-full h-full"
                    controls
                    src="{{ $this->getVideoUrl() }}"
                >
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>

        <!-- WaveSurfer Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Waveform</h3>
            <div id="waveform" class="w-full"></div>
            <div class="mt-4 flex gap-2">
                <button
                    type="button"
                    id="play-pause-btn"
                    class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700"
                >
                    Play/Pause
                </button>
                <button
                    type="button"
                    id="add-chapter-btn"
                    class="px-4 py-2 bg-success-600 text-white rounded hover:bg-success-700"
                >
                    Add Chapter from Selection
                </button>
            </div>
            <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                <p>Select a region on the waveform to create a chapter. Drag to select start and end times.</p>
            </div>
        </div>

        <!-- Chapters Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            {{ $this->table }}
        </div>
    </div>

    @push('scripts')
        <script type="importmap">
        {
            "imports": {
                "wavesurfer.js": "https://unpkg.com/wavesurfer.js@7/dist/wavesurfer.esm.js",
                "wavesurfer.js/plugins/regions": "https://unpkg.com/wavesurfer.js@7/dist/plugins/regions.esm.js"
            }
        }
        </script>
        <script type="module">
            import WaveSurfer from 'wavesurfer.js';
            import RegionsPlugin from 'wavesurfer.js/plugins/regions';

            document.addEventListener('DOMContentLoaded', function() {
                const video = document.getElementById('video-player');
                const waveformContainer = document.getElementById('waveform');
                let wavesurfer = null;
                let regions = null;
                let currentRegion = null;
                let isSelecting = false;
                let selectionStart = null;

                // Initialize WaveSurfer with MediaElement backend (works with video files)
                wavesurfer = WaveSurfer.create({
                    container: waveformContainer,
                    waveColor: '#4f46e5',
                    progressColor: '#7c3aed',
                    cursorColor: '#ef4444',
                    barWidth: 2,
                    barRadius: 3,
                    responsive: true,
                    height: 100,
                    normalize: true,
                    backend: 'MediaElement',
                    mediaControls: false,
                    media: video, // Use the video element as the media source
                });

                // Wait for video to be loaded before initializing waveform
                video.addEventListener('loadedmetadata', () => {
                    if (video.duration) {
                        // Generate waveform from the video's audio track
                        wavesurfer.load(video);
                    }
                });

                // Sync video with WaveSurfer
                wavesurfer.on('play', () => {
                    video.play();
                });

                wavesurfer.on('pause', () => {
                    video.pause();
                });

                wavesurfer.on('seek', (progress) => {
                    if (video.duration && !isSelecting) {
                        video.currentTime = progress * video.duration;
                    }
                });

                // Sync WaveSurfer with video
                video.addEventListener('play', () => {
                    if (!wavesurfer.isPlaying()) {
                        wavesurfer.play();
                    }
                });

                video.addEventListener('pause', () => {
                    if (wavesurfer.isPlaying()) {
                        wavesurfer.pause();
                    }
                });

                video.addEventListener('timeupdate', () => {
                    if (video.duration && !isSelecting) {
                        const progress = video.currentTime / video.duration;
                        if (Math.abs(wavesurfer.getCurrentTime() / wavesurfer.getDuration() - progress) > 0.01) {
                            wavesurfer.seekTo(progress);
                        }
                    }
                });

                // Initialize Regions plugin
                regions = wavesurfer.registerPlugin(RegionsPlugin.create());

                // Load existing chapters as regions
                @if($record && $record->chapters)
                    @foreach($record->chapters as $chapter)
                    regions.addRegion({
                        start: {{ $chapter->start_time }},
                        end: {{ $chapter->end_time }},
                        color: 'rgba(34, 197, 94, 0.3)',
                        drag: true,
                        resize: true,
                        id: 'chapter-{{ $chapter->id }}',
                    });
                    @endforeach
                @endif

                // Handle region selection
                regions.on('region-clicked', (region, e) => {
                    e.stopPropagation();
                    currentRegion = region;
                    // Jump to start of region
                    wavesurfer.seekTo(region.start / wavesurfer.getDuration());
                    video.currentTime = region.start;
                });

                regions.on('region-updated', (region) => {
                    currentRegion = region;
                });

                // Handle waveform click to create selection
                waveformContainer.addEventListener('mousedown', (e) => {
                    if (e.target === waveformContainer || e.target.closest('#waveform')) {
                        isSelecting = true;
                        const rect = waveformContainer.getBoundingClientRect();
                        const x = e.clientX - rect.left;
                        const progress = x / rect.width;
                        selectionStart = progress * wavesurfer.getDuration();
                    }
                });

                waveformContainer.addEventListener('mousemove', (e) => {
                    if (isSelecting && selectionStart !== null) {
                        const rect = waveformContainer.getBoundingClientRect();
                        const x = e.clientX - rect.left;
                        const progress = Math.max(0, Math.min(1, x / rect.width));
                        const selectionEnd = progress * wavesurfer.getDuration();
                        
                        // Remove temporary selection region if exists
                        const tempRegion = regions.getRegions().find(r => r.id === 'temp-selection');
                        if (tempRegion) {
                            tempRegion.remove();
                        }
                        
                        // Create temporary selection region
                        if (selectionEnd > selectionStart) {
                            regions.addRegion({
                                start: selectionStart,
                                end: selectionEnd,
                                color: 'rgba(59, 130, 246, 0.3)',
                                drag: false,
                                resize: true,
                                id: 'temp-selection',
                            });
                            currentRegion = regions.getRegions().find(r => r.id === 'temp-selection');
                        }
                    }
                });

                waveformContainer.addEventListener('mouseup', () => {
                    isSelecting = false;
                });

                // Play/Pause button
                document.getElementById('play-pause-btn').addEventListener('click', () => {
                    wavesurfer.playPause();
                });

                // Add Chapter button
                document.getElementById('add-chapter-btn').addEventListener('click', () => {
                    const tempRegion = regions.getRegions().find(r => r.id === 'temp-selection');
                    const selectedRegion = tempRegion || currentRegion;
                    
                    if (selectedRegion && selectedRegion.id === 'temp-selection') {
                        const startTime = selectedRegion.start;
                        const endTime = selectedRegion.end;

                        // Call Livewire method to add chapter
                        @this.call('addChapter', startTime, endTime).then(() => {
                            // Remove temp region
                            selectedRegion.remove();
                            // Reload the page to refresh regions
                            window.location.reload();
                        });
                    } else if (selectedRegion) {
                        alert('Please select a new region on the waveform by clicking and dragging, or use an existing chapter region.');
                    } else {
                        alert('Please select a region on the waveform first by clicking and dragging.');
                    }
                });

                // Ensure waveform is ready
                wavesurfer.on('ready', () => {
                    console.log('Waveform ready, duration:', wavesurfer.getDuration());
                });

                wavesurfer.on('error', (error) => {
                    console.error('WaveSurfer error:', error);
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
