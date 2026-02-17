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
                    preload="metadata"
                    crossorigin="anonymous"
                >
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>

        <!-- Timeline Editor Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Timeline & Chapters</h3>
            
            <!-- Playback Controls -->
            <div class="mb-4 flex items-center gap-4">
                <button
                    type="button"
                    id="play-pause-btn"
                    class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 flex items-center gap-2"
                >
                    <span id="play-icon">▶</span>
                    <span id="pause-icon" class="hidden">⏸</span>
                    <span>Play/Pause</span>
                </button>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Time:</span>
                    <span id="current-time" class="text-sm font-mono">00:00</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">/</span>
                    <span id="total-time" class="text-sm font-mono">00:00</span>
                </div>
            </div>

            <!-- Timeline Ruler -->
            <div id="timeline-ruler" style="width: 100%; position: relative; border-bottom: 2px solid #d1d5db; cursor: pointer; margin-bottom: 8px; height: 4px; min-height: 45px; background: linear-gradient(to bottom, #f9fafb 0%, #ffffff 100%); overflow: visible; display: block; visibility: visible;">
                <div id="ruler-markers" style="position: relative; width: 100%; height: 100%; min-height: 45px; overflow: visible; display: block; visibility: visible;"></div>
                <div id="playhead-line" style="position: absolute; top: 0; bottom: 0; width: 2px; background-color: #ef4444; z-index: 20; pointer-events: none; left: 0%;">
                    <div style="position: absolute; top: -8px; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 6px solid transparent; border-right: 6px solid transparent; border-top: 6px solid #ef4444;"></div>
                </div>
            </div>
            
            <!-- Waveform Container -->
            <div id="waveform-wrapper" style="position: relative; margin-bottom: 8px;">
                <div id="waveform" style="width: 100%; min-height: 120px; background: #f3f4f6; position: relative; display: block;"></div>
                <div id="waveform-playhead" style="position: absolute; top: 0; bottom: 0; width: 2px; background-color: #ef4444; z-index: 10; pointer-events: none; left: 0%;"></div>
                <div id="waveform-loading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; background-color: #f9fafb; z-index: 30;">
                    <div style="text-align: center;">
                        <div style="display: inline-block; width: 32px; height: 32px; border: 2px solid #2563eb; border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 8px;"></div>
                        <p style="font-size: 0.875rem; color: #4b5563;">Loading waveform...</p>
                    </div>
                </div>
                <div id="waveform-error" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; align-items: center; justify-content: center; background-color: rgba(254, 242, 242, 0.5); z-index: 30;">
                    <div style="text-align: center; padding: 16px;">
                        <p style="font-size: 0.875rem; color: #dc2626;">Waveform could not be loaded. Timeline will still work.</p>
                    </div>
                </div>
            </div>
            <style>
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            </style>
            
            <!-- Timeline Tracks - Video Thumbnails -->
            <div id="thumbnails-track-container" class="w-full border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden mb-4" style="min-height: 100px; background: #ffffff;">
                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Video Thumbnails</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400" id="thumbnails-status">Generating thumbnails...</span>
                </div>
                <div id="thumbnails-track" class="relative w-full" style="min-height: 80px; background: #fafafa; overflow-x: auto; overflow-y: hidden;">
                    <!-- Video thumbnails will be rendered here -->
                </div>
            </div>
            
            <!-- Timeline Tracks - Chapters Visualization -->
            <div id="timeline-tracks" class="w-full border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden mb-4" style="min-height: 80px; background: #ffffff;">
                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Chapters Track</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Click and drag on waveform to select a range</span>
                </div>
                <div id="chapters-track" class="relative w-full" style="min-height: 60px; background: #fafafa;">
                    <!-- Chapter blocks will be rendered here -->
                </div>
            </div>
            
            <!-- Selection Info -->
            <div id="selection-info" class="hidden mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium text-blue-900 dark:text-blue-200">Selected Range:</span>
                        <span id="selection-range" class="text-sm text-blue-700 dark:text-blue-300 ml-2 font-mono"></span>
                    </div>
                    <button
                        type="button"
                        id="add-chapter-btn"
                        class="px-4 py-2 bg-success-600 text-white rounded hover:bg-success-700 text-sm"
                    >
                        Create Chapter
                    </button>
                </div>
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
            (function() {
                'use strict';
                
                console.log('Script starting...');
                
                // Wait for DOM to be fully ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    setTimeout(init, 100);
                }
                
                function init() {
                    console.log('Initializing timeline editor...');
                    
                    const video = document.getElementById('video-player');
                    const waveformContainer = document.getElementById('waveform');
                    const rulerMarkers = document.getElementById('ruler-markers');
                    const playheadLine = document.getElementById('playhead-line');
                    const waveformPlayhead = document.getElementById('waveform-playhead');
                    const chaptersTrack = document.getElementById('chapters-track');
                    const currentTimeEl = document.getElementById('current-time');
                    const totalTimeEl = document.getElementById('total-time');
                    const selectionInfo = document.getElementById('selection-info');
                    const selectionRange = document.getElementById('selection-range');
                    const playIcon = document.getElementById('play-icon');
                    const pauseIcon = document.getElementById('pause-icon');
                    const loadingEl = document.getElementById('waveform-loading');
                    const errorEl = document.getElementById('waveform-error');
                    
                    if (!video || !waveformContainer) {
                        console.error('Required elements not found');
                        return;
                    }
                    
                    let wavesurfer = null;
                    let regions = null;
                    let currentRegion = null;
                    let isSelecting = false;
                    let selectionStart = null;
                    let videoDuration = 0;
                    let isDraggingPlayhead = false;
                    const videoUrl = '{{ $this->getVideoUrl() }}';
                    
                    console.log('Video URL:', videoUrl);

                    // Format time helper
                    function formatTime(seconds) {
                        const hours = Math.floor(seconds / 3600);
                        const minutes = Math.floor((seconds % 3600) / 60);
                        const secs = Math.floor(seconds % 60);
                        
                        if (hours > 0) {
                            return `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                        }
                        return `${minutes}:${String(secs).padStart(2, '0')}`;
                    }

                    // Update time display
                    function updateTimeDisplay() {
                        if (video.duration && totalTimeEl) {
                            totalTimeEl.textContent = formatTime(video.duration);
                        }
                        if (video.currentTime !== undefined && currentTimeEl) {
                            currentTimeEl.textContent = formatTime(video.currentTime);
                        }
                    }

                    // Create timeline ruler with time markers
                    function createTimelineRuler(duration) {
                        if (!duration || duration <= 0) {
                            console.warn('Cannot create timeline ruler: invalid duration', duration);
                            return;
                        }
                        
                        if (!rulerMarkers) {
                            console.error('Cannot create timeline ruler: rulerMarkers element not found');
                            return;
                        }
                        
                        videoDuration = duration;
                        rulerMarkers.innerHTML = '';
                        
                        // Force a layout recalculation
                        const ruler = document.getElementById('timeline-ruler');
                        if (ruler) {
                            ruler.style.display = 'block';
                            ruler.style.visibility = 'visible';
                            ruler.style.opacity = '1';
                            ruler.style.height = '45px';
                        }
                        
                        // Use requestAnimationFrame to ensure layout is calculated
                        requestAnimationFrame(() => {
                            // Try multiple methods to get width
                            let containerWidth = rulerMarkers.offsetWidth;
                            if (!containerWidth || containerWidth === 0) {
                                containerWidth = rulerMarkers.parentElement?.offsetWidth;
                            }
                            if (!containerWidth || containerWidth === 0) {
                                containerWidth = waveformContainer?.offsetWidth;
                            }
                            if (!containerWidth || containerWidth === 0) {
                                containerWidth = document.querySelector('#timeline-ruler')?.offsetWidth;
                            }
                            // Fallback to window width if still 0
                            if (!containerWidth || containerWidth === 0) {
                                containerWidth = window.innerWidth - 100; // Approximate, accounting for sidebar
                            }
                            
                            console.log('Creating timeline ruler for duration:', duration, 'width:', containerWidth, {
                                rulerMarkersWidth: rulerMarkers.offsetWidth,
                                parentWidth: rulerMarkers.parentElement?.offsetWidth,
                                waveformWidth: waveformContainer?.offsetWidth,
                                rulerWidth: document.querySelector('#timeline-ruler')?.offsetWidth
                            });
                            
                            const interval = getOptimalInterval(duration, containerWidth);
                            
                            // Create major markers
                            for (let time = 0; time <= duration; time += interval) {
                                const marker = document.createElement('div');
                                marker.style.position = 'absolute';
                                marker.style.top = '0';
                                marker.style.left = `${(time / duration) * 100}%`;
                                marker.style.transform = 'translateX(-50%)';
                                marker.style.display = 'flex';
                                marker.style.flexDirection = 'column';
                                marker.style.alignItems = 'flex-start';
                                marker.style.zIndex = '5';
                                
                                // Major line
                                const line = document.createElement('div');
                                line.style.width = '2px';
                                line.style.height = '20px';
                                line.style.backgroundColor = '#4b5563'; // gray-600
                                marker.appendChild(line);
                                
                                // Time label
                                const label = document.createElement('span');
                                label.style.fontSize = '0.75rem';
                                label.style.fontWeight = '600';
                                label.style.color = '#1f2937'; // gray-800
                                label.style.marginTop = '4px';
                                label.style.whiteSpace = 'nowrap';
                                label.style.userSelect = 'none';
                                label.textContent = formatTime(time);
                                marker.appendChild(label);
                                
                                rulerMarkers.appendChild(marker);
                                
                                // Minor markers
                                if (interval > 1) {
                                    const minorInterval = interval / 5;
                                    for (let i = 1; i < 5; i++) {
                                        const minorTime = time + (minorInterval * i);
                                        if (minorTime < duration) {
                                            const minorMarker = document.createElement('div');
                                            minorMarker.style.position = 'absolute';
                                            minorMarker.style.top = '0';
                                            minorMarker.style.left = `${(minorTime / duration) * 100}%`;
                                            minorMarker.style.transform = 'translateX(-50%)';
                                            
                                            const minorLine = document.createElement('div');
                                            minorLine.style.width = '1px';
                                            minorLine.style.height = '10px';
                                            minorLine.style.backgroundColor = '#9ca3af'; // gray-400
                                            minorMarker.appendChild(minorLine);
                                            
                                            rulerMarkers.appendChild(minorMarker);
                                        }
                                    }
                                }
                            }
                            
                            console.log('Timeline ruler created successfully with', Math.floor(duration / interval) + 1, 'major markers');
                            
                            // Force visibility
                            if (ruler) {
                                ruler.style.display = 'block';
                                ruler.style.visibility = 'visible';
                                ruler.style.opacity = '1';
                            }
                            if (rulerMarkers) {
                                rulerMarkers.style.display = 'block';
                                rulerMarkers.style.visibility = 'visible';
                                rulerMarkers.style.position = 'relative';
                                rulerMarkers.style.width = '100%';
                                rulerMarkers.style.height = '100%';
                            }
                        });
                    }
                    
                    // Create simple waveform visualization
                    function createSimpleWaveform() {
                        if (!waveformContainer || !videoDuration) {
                            console.warn('Cannot create waveform:', {waveformContainer, videoDuration});
                            return;
                        }
                        
                        // Clear existing waveform bars
                        const existingBars = waveformContainer.querySelectorAll('.waveform-bar');
                        existingBars.forEach(bar => bar.remove());
                        
                        // Create a simple bar-based waveform visualization
                        const bars = 150; // Number of bars
                        const containerWidth = waveformContainer.offsetWidth || 1000;
                        const barWidth = Math.max(2, containerWidth / bars);
                        
                        console.log('Creating simple waveform with', bars, 'bars, width:', containerWidth);
                        
                        for (let i = 0; i < bars; i++) {
                            const bar = document.createElement('div');
                            bar.className = 'waveform-bar';
                            bar.style.position = 'absolute';
                            bar.style.left = `${(i / bars) * 100}%`;
                            bar.style.width = `${barWidth}px`;
                            bar.style.height = `${30 + Math.random() * 70}px`; // Random height
                            bar.style.backgroundColor = '#6366f1'; // indigo-500
                            bar.style.bottom = '0';
                            bar.style.borderRadius = '2px';
                            bar.style.pointerEvents = 'none'; // Allow clicks to pass through
                            waveformContainer.appendChild(bar);
                        }
                        
                        console.log('Simple waveform created');
                    }

                    // Extract video frame at specific time
                    function extractFrameAtTime(video, time) {
                        return new Promise((resolve, reject) => {
                            const canvas = document.createElement('canvas');
                            const ctx = canvas.getContext('2d');
                            
                            // Store original time
                            const originalTime = video.currentTime;
                            
                            // Set video to desired time
                            video.currentTime = time;
                            
                            // Wait for video to seek to that time
                            const onSeeked = () => {
                                try {
                                    // Set canvas dimensions to match video
                                    canvas.width = video.videoWidth || 320;
                                    canvas.height = video.videoHeight || 180;
                                    
                                    // Draw video frame to canvas
                                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                                    
                                    // Convert to data URL
                                    const imageData = canvas.toDataURL('image/jpeg', 0.7);
                                    
                                    // Restore original time
                                    video.currentTime = originalTime;
                                    
                                    resolve(imageData);
                                } catch (error) {
                                    video.currentTime = originalTime;
                                    reject(error);
                                }
                            };
                            
                            // Handle seek errors
                            const onError = () => {
                                video.removeEventListener('seeked', onSeeked);
                                video.removeEventListener('error', onError);
                                video.currentTime = originalTime;
                                reject(new Error('Failed to seek video'));
                            };
                            
                            video.addEventListener('seeked', onSeeked, { once: true });
                            video.addEventListener('error', onError, { once: true });
                            
                            // Timeout fallback
                            setTimeout(() => {
                                video.removeEventListener('seeked', onSeeked);
                                video.removeEventListener('error', onError);
                                video.currentTime = originalTime;
                                reject(new Error('Seek timeout'));
                            }, 2000);
                        });
                    }

                    // Generate video thumbnails for timeline
                    let thumbnailGenerationInProgress = false;
                    let thumbnailsCache = new Map();
                    
                    async function generateVideoThumbnails() {
                        if (!video || !videoDuration || videoDuration <= 0) {
                            console.warn('Cannot generate thumbnails: invalid video or duration');
                            return;
                        }
                        
                        const thumbnailsTrack = document.getElementById('thumbnails-track');
                        const thumbnailsStatus = document.getElementById('thumbnails-status');
                        
                        if (!thumbnailsTrack) {
                            console.error('Thumbnails track not found');
                            return;
                        }
                        
                        if (thumbnailGenerationInProgress) {
                            console.log('Thumbnail generation already in progress');
                            return;
                        }
                        
                        thumbnailGenerationInProgress = true;
                        
                        if (thumbnailsStatus) {
                            thumbnailsStatus.textContent = 'Generating thumbnails...';
                        }
                        
                        // Clear existing thumbnails
                        thumbnailsTrack.innerHTML = '';
                        
                        // Calculate number of thumbnails based on video duration and container width
                        const containerWidth = thumbnailsTrack.offsetWidth || 1000;
                        const thumbnailWidth = 120; // Fixed width per thumbnail
                        const maxThumbnails = Math.floor(containerWidth / thumbnailWidth);
                        const minThumbnails = Math.min(20, maxThumbnails); // At least 20 thumbnails
                        const numThumbnails = Math.max(minThumbnails, Math.min(maxThumbnails, Math.floor(videoDuration / 2))); // One thumbnail per 2 seconds max
                        
                        const interval = videoDuration / numThumbnails;
                        
                        console.log('Generating', numThumbnails, 'thumbnails at', interval.toFixed(2), 'second intervals');
                        
                        // Create container for thumbnails with proper width
                        const thumbnailsContainer = document.createElement('div');
                        thumbnailsContainer.style.position = 'relative';
                        thumbnailsContainer.style.width = `${numThumbnails * thumbnailWidth}px`;
                        thumbnailsContainer.style.height = '80px';
                        thumbnailsContainer.style.display = 'flex';
                        thumbnailsContainer.style.flexDirection = 'row';
                        thumbnailsTrack.appendChild(thumbnailsContainer);
                        
                        let generated = 0;
                        let failed = 0;
                        
                        // Generate thumbnails sequentially to avoid overwhelming the browser
                        async function generateNextThumbnail(index) {
                            if (index >= numThumbnails) {
                                thumbnailGenerationInProgress = false;
                                if (thumbnailsStatus) {
                                    thumbnailsStatus.textContent = `${generated} thumbnails generated`;
                                }
                                console.log('Thumbnail generation complete:', generated, 'generated,', failed, 'failed');
                                return;
                            }
                            
                            const time = index * interval;
                            
                            // Check cache first
                            const cacheKey = Math.floor(time);
                            if (thumbnailsCache.has(cacheKey)) {
                                const cachedThumbnail = thumbnailsCache.get(cacheKey);
                                createThumbnailElement(cachedThumbnail, time, index, thumbnailWidth);
                                generated++;
                                // Generate next immediately for cached thumbnails
                                setTimeout(() => generateNextThumbnail(index + 1), 10);
                                return;
                            }
                            
                            try {
                                const imageData = await extractFrameAtTime(video, time);
                                
                                // Cache the thumbnail
                                thumbnailsCache.set(cacheKey, imageData);
                                
                                // Create thumbnail element
                                createThumbnailElement(imageData, time, index, thumbnailWidth);
                                
                                generated++;
                                
                                if (thumbnailsStatus && index % 5 === 0) {
                                    thumbnailsStatus.textContent = `Generating... ${generated}/${numThumbnails}`;
                                }
                                
                                // Small delay to prevent browser freezing
                                setTimeout(() => generateNextThumbnail(index + 1), 50);
                            } catch (error) {
                                console.warn('Failed to extract frame at', time, 'seconds:', error);
                                failed++;
                                
                                // Create placeholder for failed thumbnail
                                createThumbnailPlaceholder(time, index, thumbnailWidth);
                                
                                // Continue with next thumbnail
                                setTimeout(() => generateNextThumbnail(index + 1), 50);
                            }
                        }
                        
                        // Start generating thumbnails
                        generateNextThumbnail(0);
                    }
                    
                    function createThumbnailElement(imageData, time, index, width) {
                        const thumbnailsContainer = document.querySelector('#thumbnails-track > div');
                        if (!thumbnailsContainer) return;
                        
                        const thumbnailDiv = document.createElement('div');
                        thumbnailDiv.style.position = 'relative';
                        thumbnailDiv.style.width = `${width}px`;
                        thumbnailDiv.style.height = '80px';
                        thumbnailDiv.style.flexShrink = '0';
                        thumbnailDiv.style.borderRight = '1px solid #e5e7eb';
                        thumbnailDiv.style.cursor = 'pointer';
                        thumbnailDiv.style.overflow = 'hidden';
                        thumbnailDiv.dataset.time = time;
                        
                        const img = document.createElement('img');
                        img.src = imageData;
                        img.style.width = '100%';
                        img.style.height = '100%';
                        img.style.objectFit = 'cover';
                        img.style.display = 'block';
                        thumbnailDiv.appendChild(img);
                        
                        // Time label overlay
                        const timeLabel = document.createElement('div');
                        timeLabel.style.position = 'absolute';
                        timeLabel.style.bottom = '0';
                        timeLabel.style.left = '0';
                        timeLabel.style.right = '0';
                        timeLabel.style.background = 'linear-gradient(to top, rgba(0,0,0,0.7), transparent)';
                        timeLabel.style.color = '#ffffff';
                        timeLabel.style.fontSize = '10px';
                        timeLabel.style.padding = '2px 4px';
                        timeLabel.style.textAlign = 'center';
                        timeLabel.textContent = formatTime(time);
                        thumbnailDiv.appendChild(timeLabel);
                        
                        // Click handler to seek video
                        thumbnailDiv.addEventListener('click', () => {
                            video.currentTime = time;
                            if (wavesurfer && wavesurfer.getDuration() > 0) {
                                wavesurfer.seekTo(time / wavesurfer.getDuration());
                            }
                            updatePlayhead(time / videoDuration);
                        });
                        
                        // Hover effect
                        thumbnailDiv.addEventListener('mouseenter', () => {
                            thumbnailDiv.style.transform = 'scale(1.05)';
                            thumbnailDiv.style.zIndex = '20';
                            thumbnailDiv.style.transition = 'transform 0.2s';
                        });
                        
                        thumbnailDiv.addEventListener('mouseleave', () => {
                            thumbnailDiv.style.transform = 'scale(1)';
                            thumbnailDiv.style.zIndex = '1';
                        });
                        
                        thumbnailsContainer.appendChild(thumbnailDiv);
                    }
                    
                    function createThumbnailPlaceholder(time, index, width) {
                        const thumbnailsContainer = document.querySelector('#thumbnails-track > div');
                        if (!thumbnailsContainer) return;
                        
                        const placeholderDiv = document.createElement('div');
                        placeholderDiv.style.position = 'relative';
                        placeholderDiv.style.width = `${width}px`;
                        placeholderDiv.style.height = '80px';
                        placeholderDiv.style.flexShrink = '0';
                        placeholderDiv.style.borderRight = '1px solid #e5e7eb';
                        placeholderDiv.style.background = '#e5e7eb';
                        placeholderDiv.style.display = 'flex';
                        placeholderDiv.style.alignItems = 'center';
                        placeholderDiv.style.justifyContent = 'center';
                        placeholderDiv.style.color = '#9ca3af';
                        placeholderDiv.style.fontSize = '10px';
                        placeholderDiv.textContent = formatTime(time);
                        placeholderDiv.dataset.time = time;
                        
                        thumbnailsContainer.appendChild(placeholderDiv);
                    }

                    function getOptimalInterval(duration, width) {
                        if (!width || width <= 0) return 10;
                        const pixelsPerSecond = width / duration;
                        if (pixelsPerSecond > 20) return 1;
                        if (pixelsPerSecond > 10) return 5;
                        if (pixelsPerSecond > 5) return 10;
                        if (pixelsPerSecond > 2) return 30;
                        if (pixelsPerSecond > 1) return 60;
                        return Math.max(60, Math.ceil(duration / 20) * 10);
                    }

                    // Update playhead position
                    function updatePlayhead(progress) {
                        if (!playheadLine || !waveformPlayhead) return;
                        const position = `${progress * 100}%`;
                        playheadLine.style.left = position;
                        waveformPlayhead.style.left = position;
                    }

                    // Setup playhead dragging
                    function setupPlayheadDragging() {
                        const ruler = document.getElementById('timeline-ruler');
                        const waveformWrapper = document.getElementById('waveform-wrapper');
                        
                        if (!ruler || !waveformWrapper) return;
                        
                        const updatePlayheadFromEvent = (e, element) => {
                            const rect = element.getBoundingClientRect();
                            const x = e.clientX - rect.left;
                            const progress = Math.max(0, Math.min(1, x / rect.width));
                            
                            if (videoDuration > 0) {
                                const time = progress * videoDuration;
                                video.currentTime = time;
                                if (wavesurfer) {
                                    wavesurfer.seekTo(progress);
                                }
                                updatePlayhead(progress);
                                updateTimeDisplay();
                            }
                        };
                        
                        const startDrag = (e) => {
                            if (e.target.closest('#waveform')) return;
                            isDraggingPlayhead = true;
                            updatePlayheadFromEvent(e, e.currentTarget);
                            e.preventDefault();
                        };
                        
                        const drag = (e) => {
                            if (isDraggingPlayhead) {
                                const target = e.target.closest('#timeline-ruler') || e.target.closest('#waveform-wrapper');
                                if (target) {
                                    updatePlayheadFromEvent(e, target);
                                }
                            }
                        };
                        
                        const endDrag = () => {
                            isDraggingPlayhead = false;
                        };
                        
                        ruler.addEventListener('mousedown', startDrag);
                        waveformWrapper.addEventListener('mousedown', (e) => {
                            if (!e.target.closest('#waveform')) {
                                startDrag(e);
                            }
                        });
                        document.addEventListener('mousemove', drag);
                        document.addEventListener('mouseup', endDrag);
                    }

                    // Render chapters as visual blocks on timeline
                    function renderChaptersOnTimeline() {
                        if (!videoDuration || videoDuration <= 0 || !chaptersTrack) {
                            console.warn('Cannot render chapters:', {videoDuration, chaptersTrack});
                            return;
                        }
                        
                        console.log('Rendering chapters on timeline, duration:', videoDuration);
                        chaptersTrack.innerHTML = '';
                        
                        @if($record && $record->chapters)
                            const chapters = @json($record->chapters);
                            console.log('Chapters to render:', chapters.length);
                            
                            chapters.forEach((chapter, index) => {
                                const startPercent = (chapter.start_time / videoDuration) * 100;
                                const widthPercent = ((chapter.end_time - chapter.start_time) / videoDuration) * 100;
                                
                                console.log(`Chapter ${index + 1}:`, {
                                    start: chapter.start_time,
                                    end: chapter.end_time,
                                    startPercent,
                                    widthPercent
                                });
                                
                                const chapterBlock = document.createElement('div');
                                chapterBlock.className = 'absolute top-1 bottom-1 rounded-md border-2 border-green-500 bg-gradient-to-r from-green-100 to-green-50 dark:from-green-900/40 dark:to-green-800/30 cursor-pointer hover:shadow-lg transition-shadow';
                                chapterBlock.style.left = `${startPercent}%`;
                                chapterBlock.style.width = `${widthPercent}%`;
                                chapterBlock.style.minWidth = '60px'; // Ensure minimum width for visibility
                                chapterBlock.style.zIndex = '10';
                                chapterBlock.title = `${chapter.title}\n${formatTime(chapter.start_time)} - ${formatTime(chapter.end_time)}`;
                                
                                // Chapter label
                                const chapterLabel = document.createElement('div');
                                chapterLabel.className = 'px-2 py-1 text-xs font-semibold text-green-900 dark:text-green-100 truncate';
                                chapterLabel.textContent = `${index + 1}. ${chapter.title}`;
                                chapterBlock.appendChild(chapterLabel);
                                
                                // Time label
                                const timeLabel = document.createElement('div');
                                timeLabel.className = 'px-2 text-xs text-green-700 dark:text-green-300';
                                timeLabel.textContent = `${formatTime(chapter.start_time)} - ${formatTime(chapter.end_time)}`;
                                chapterBlock.appendChild(timeLabel);
                                
                                // Click handler to jump to chapter start
                                chapterBlock.addEventListener('click', () => {
                                    video.currentTime = chapter.start_time;
                                    if (wavesurfer && wavesurfer.getDuration() > 0) {
                                        wavesurfer.seekTo(chapter.start_time / wavesurfer.getDuration());
                                    }
                                    updatePlayhead(chapter.start_time / videoDuration);
                                });
                                
                                chaptersTrack.appendChild(chapterBlock);
                            });
                            
                            console.log('Chapters rendered successfully');
                        @endif
                    }

                    // Show/hide selection info
                    function updateSelectionInfo(start, end) {
                        if (start !== null && end !== null && start < end && selectionRange && selectionInfo) {
                            selectionRange.textContent = `${formatTime(start)} → ${formatTime(end)}`;
                            selectionInfo.classList.remove('hidden');
                        } else if (selectionInfo) {
                            selectionInfo.classList.add('hidden');
                        }
                    }

                    // Initialize WaveSurfer
                    async function initializeWaveSurfer() {
                        if (wavesurfer) {
                            console.log('WaveSurfer already initialized');
                            return;
                        }
                        
                        console.log('Initializing WaveSurfer...');
                        console.log('Container:', waveformContainer);
                        console.log('Container dimensions:', waveformContainer.offsetWidth, 'x', waveformContainer.offsetHeight);
                        console.log('Video URL:', videoUrl);
                        
                        if (!waveformContainer || waveformContainer.offsetWidth === 0) {
                            console.warn('Waveform container not ready, retrying...');
                            setTimeout(() => initializeWaveSurfer(), 500);
                            return;
                        }
                        
                        try {
                            // Import WaveSurfer dynamically
                            const { default: WaveSurfer } = await import('wavesurfer.js');
                            const { default: RegionsPlugin } = await import('wavesurfer.js/plugins/regions');
                            
                            console.log('WaveSurfer modules loaded');
                            
                            // Use MediaElement backend for video files - it doesn't generate waveform but syncs playback
                            // For actual waveform visualization, we'll create a simple visual representation
                            wavesurfer = WaveSurfer.create({
                                container: waveformContainer,
                                waveColor: '#6366f1',
                                progressColor: '#8b5cf6',
                                cursorColor: '#ef4444',
                                barWidth: 3,
                                barRadius: 2,
                                responsive: true,
                                height: 120,
                                normalize: true,
                                backend: 'MediaElement',
                                mediaControls: false,
                                media: video,
                                // Don't try to generate waveform from video
                                interact: true,
                            });

                            console.log('WaveSurfer instance created with MediaElement backend');
                            
                            // Create a simple visual waveform representation since MediaElement doesn't generate one
                            // Do this immediately, don't wait for WaveSurfer ready event
                            setTimeout(() => {
                                createSimpleWaveform();
                            }, 200);

                            wavesurfer.on('ready', () => {
                                console.log('Waveform ready, duration:', wavesurfer.getDuration());
                                
                                if (loadingEl) loadingEl.style.display = 'none';
                                
                                const duration = wavesurfer.getDuration() || videoDuration;
                                if (duration > 0) {
                                    if (duration !== videoDuration) {
                                        videoDuration = duration;
                                        createTimelineRuler(duration);
                                    }
                                    renderChaptersOnTimeline();
                                }
                                
                                // Initialize Regions plugin
                                if (!regions) {
                                    regions = wavesurfer.registerPlugin(RegionsPlugin.create());
                                    
                                    @if($record && $record->chapters)
                                        const chapters = @json($record->chapters);
                                        console.log('Adding', chapters.length, 'chapters as regions');
                                        chapters.forEach((chapter) => {
                                            try {
                                                regions.addRegion({
                                                    start: parseFloat(chapter.start_time),
                                                    end: parseFloat(chapter.end_time),
                                                    color: 'rgba(34, 197, 94, 0.5)',
                                                    drag: true,
                                                    resize: true,
                                                    id: 'chapter-' + chapter.id,
                                                });
                                                console.log('Added region for chapter', chapter.id);
                                            } catch (error) {
                                                console.error('Error adding region for chapter', chapter.id, error);
                                            }
                                        });
                                    @endif

                                    regions.on('region-clicked', (region, e) => {
                                        e.stopPropagation();
                                        currentRegion = region;
                                        if (wavesurfer && videoDuration > 0) {
                                            const duration = wavesurfer.getDuration() || videoDuration;
                                            wavesurfer.seekTo(region.start / duration);
                                            video.currentTime = region.start;
                                            updatePlayhead(region.start / videoDuration);
                                        }
                                    });

                                    regions.on('region-updated', (region) => {
                                        currentRegion = region;
                                    });
                                    
                                    setupWaveformSelection();
                                }
                            });

                            wavesurfer.on('play', () => {
                                if (video.paused) video.play();
                                if (playIcon) playIcon.classList.add('hidden');
                                if (pauseIcon) pauseIcon.classList.remove('hidden');
                            });

                            wavesurfer.on('pause', () => {
                                if (!video.paused) video.pause();
                                if (playIcon) playIcon.classList.remove('hidden');
                                if (pauseIcon) pauseIcon.classList.add('hidden');
                            });

                            wavesurfer.on('seek', (progress) => {
                                if (!isDraggingPlayhead && video.duration && !isSelecting) {
                                    video.currentTime = progress * video.duration;
                                    updatePlayhead(progress);
                                    updateTimeDisplay();
                                }
                            });

                            wavesurfer.on('error', (error) => {
                                console.error('WaveSurfer error:', error);
                                if (loadingEl) loadingEl.style.display = 'none';
                                
                                // Hide error message - we'll use simple waveform instead
                                if (errorEl) errorEl.style.display = 'none';
                                
                                // Create simple waveform visualization as fallback
                                setTimeout(() => {
                                    if (videoDuration > 0) {
                                        createSimpleWaveform();
                                        setupWaveformSelection(); // Setup selection after waveform is created
                                    }
                                }, 100);
                                
                                // Timeline should already be showing from video metadata
                                // Just ensure it's there
                                if (video.duration && video.duration > 0 && !videoDuration) {
                                    videoDuration = video.duration;
                                    createTimelineRuler(video.duration);
                                    renderChaptersOnTimeline();
                                }
                            });
                            
                        } catch (error) {
                            console.error('Failed to initialize WaveSurfer:', error);
                            if (loadingEl) loadingEl.style.display = 'none';
                            if (errorEl) errorEl.style.display = 'none';
                            
                            // Create simple waveform as fallback
                            setTimeout(() => {
                                if (videoDuration > 0) {
                                    createSimpleWaveform();
                                    setupWaveformSelection();
                                }
                            }, 200);
                            
                            // Still show timeline even if waveform fails
                            if (video.duration) {
                                createTimelineRuler(video.duration);
                                renderChaptersOnTimeline();
                            }
                        }
                    }

                    // Setup waveform selection (works with or without WaveSurfer)
                    let selectionSetupDone = false;
                    function setupWaveformSelection() {
                        if (!waveformContainer || !videoDuration) {
                            console.warn('Cannot setup waveform selection:', {waveformContainer, videoDuration});
                            return;
                        }
                        
                        if (selectionSetupDone) {
                            console.log('Selection already setup, skipping');
                            return;
                        }
                        
                        console.log('Setting up waveform selection');
                        selectionSetupDone = true;
                        
                        const updatedContainer = document.getElementById('waveform');
                        if (!updatedContainer) {
                            console.error('Waveform container not found');
                            return;
                        }
                        
                        updatedContainer.addEventListener('mousedown', (e) => {
                            // Don't start selection if clicking on playhead or selection block
                            if (e.target.closest('#playhead-line') || 
                                e.target.closest('#waveform-playhead') ||
                                e.target.id === 'temp-selection-visual') {
                                return;
                            }
                            
                            // Start selection when clicking anywhere on waveform
                            isSelecting = true;
                            const rect = updatedContainer.getBoundingClientRect();
                            const x = e.clientX - rect.left;
                            const progress = Math.max(0, Math.min(1, x / rect.width));
                            selectionStart = progress * videoDuration;
                            updateSelectionInfo(selectionStart, null);
                            console.log('Selection started at:', selectionStart.toFixed(2), 'seconds');
                            e.preventDefault();
                            e.stopPropagation();
                        });

                        updatedContainer.addEventListener('mousemove', (e) => {
                            if (isSelecting && selectionStart !== null && videoDuration > 0) {
                                const rect = updatedContainer.getBoundingClientRect();
                                const x = e.clientX - rect.left;
                                const progress = Math.max(0, Math.min(1, x / rect.width));
                                const selectionEnd = progress * videoDuration;
                                
                                // Remove existing temp selection visual
                                const existingSelection = document.getElementById('temp-selection-visual');
                                if (existingSelection) existingSelection.remove();
                                
                                // Remove temp region if WaveSurfer regions exist
                                if (regions) {
                                    const tempRegion = regions.getRegions().find(r => r.id === 'temp-selection');
                                    if (tempRegion) tempRegion.remove();
                                }
                                
                                if (selectionEnd > selectionStart) {
                                    // Create visual selection block
                                    const selectionBlock = document.createElement('div');
                                    selectionBlock.id = 'temp-selection-visual';
                                    selectionBlock.style.position = 'absolute';
                                    selectionBlock.style.left = `${(selectionStart / videoDuration) * 100}%`;
                                    selectionBlock.style.width = `${((selectionEnd - selectionStart) / videoDuration) * 100}%`;
                                    selectionBlock.style.top = '0';
                                    selectionBlock.style.bottom = '0';
                                    selectionBlock.style.backgroundColor = 'rgba(59, 130, 246, 0.4)';
                                    selectionBlock.style.border = '2px solid #3b82f6';
                                    selectionBlock.style.borderRadius = '4px';
                                    selectionBlock.style.zIndex = '15';
                                    selectionBlock.style.pointerEvents = 'none';
                                    selectionBlock.dataset.start = selectionStart;
                                    selectionBlock.dataset.end = selectionEnd;
                                    updatedContainer.appendChild(selectionBlock);
                                    
                                    // Add region if WaveSurfer is working
                                    if (regions && wavesurfer) {
                                        try {
                                            regions.addRegion({
                                                start: selectionStart,
                                                end: selectionEnd,
                                                color: 'rgba(59, 130, 246, 0.4)',
                                                drag: false,
                                                resize: true,
                                                id: 'temp-selection',
                                            });
                                            currentRegion = regions.getRegions().find(r => r.id === 'temp-selection');
                                        } catch (error) {
                                            console.warn('Could not add WaveSurfer region:', error);
                                        }
                                    }
                                    
                                    // Store selection for chapter creation
                                    if (!currentRegion) {
                                        currentRegion = {
                                            id: 'temp-selection',
                                            start: selectionStart,
                                            end: selectionEnd
                                        };
                                    }
                                    
                                    updateSelectionInfo(selectionStart, selectionEnd);
                                }
                            }
                        });

                        updatedContainer.addEventListener('mouseup', (e) => {
                            if (isSelecting) {
                                console.log('Selection ended');
                                isSelecting = false;
                                e.preventDefault();
                                e.stopPropagation();
                            }
                        });
                        
                        // Also handle mouseleave to end selection
                        updatedContainer.addEventListener('mouseleave', () => {
                            if (isSelecting) {
                                isSelecting = false;
                            }
                        });
                        
                        console.log('Waveform selection setup complete');
                    }

                    // Video event handlers
                    video.addEventListener('play', () => {
                        if (wavesurfer && !wavesurfer.isPlaying()) {
                            wavesurfer.play();
                        }
                        if (playIcon) playIcon.classList.add('hidden');
                        if (pauseIcon) pauseIcon.classList.remove('hidden');
                    });

                    video.addEventListener('pause', () => {
                        if (wavesurfer && wavesurfer.isPlaying()) {
                            wavesurfer.pause();
                        }
                        if (playIcon) playIcon.classList.remove('hidden');
                        if (pauseIcon) pauseIcon.classList.add('hidden');
                    });

                    video.addEventListener('timeupdate', () => {
                        if (!isDraggingPlayhead && video.duration) {
                            const progress = video.currentTime / video.duration;
                            updatePlayhead(progress);
                            updateTimeDisplay();
                            if (wavesurfer && wavesurfer.getDuration() > 0) {
                                const wsProgress = wavesurfer.getCurrentTime() / wavesurfer.getDuration();
                                if (Math.abs(wsProgress - progress) > 0.01) {
                                    wavesurfer.seekTo(progress);
                                }
                            }
                        }
                    });

                    // Initialize timeline immediately when video metadata loads
                    let loadedmetadataHandled = false;
                    video.addEventListener('loadedmetadata', () => {
                        if (loadedmetadataHandled) return;
                        loadedmetadataHandled = true;
                        
                        console.log('Video metadata loaded, duration:', video.duration);
                        console.log('Video readyState:', video.readyState);
                        
                        if (video.duration && video.duration > 0) {
                            console.log('Creating timeline with video duration:', video.duration);
                            videoDuration = video.duration;
                            
                            // Force create timeline immediately
                            setTimeout(() => {
                                createTimelineRuler(video.duration);
                                renderChaptersOnTimeline();
                                setupPlayheadDragging();
                                updateTimeDisplay();
                                setupWaveformSelection(); // Setup selection here too
                            }, 100);
                            
                            // Initialize WaveSurfer after a delay (but don't wait for it)
                            setTimeout(() => {
                                initializeWaveSurfer();
                            }, 500);
                            
                            // Generate thumbnails after video metadata is loaded
                            setTimeout(() => {
                                generateVideoThumbnails();
                            }, 1000);
                        } else {
                            console.warn('Video duration is 0 or invalid');
                        }
                    });
                    
                    // Also try immediately if video already has duration
                    if (video.readyState >= 1 && video.duration > 0) {
                        console.log('Video already has duration, creating timeline immediately');
                        videoDuration = video.duration;
                        setTimeout(() => {
                            createTimelineRuler(video.duration);
                            renderChaptersOnTimeline();
                            setupPlayheadDragging();
                            updateTimeDisplay();
                            setupWaveformSelection();
                            // Generate thumbnails after a short delay to ensure everything is rendered
                            setTimeout(() => {
                                generateVideoThumbnails();
                            }, 500);
                        }, 100);
                    }
                    
                    // Also try when video can play
                    video.addEventListener('canplay', () => {
                        console.log('Video can play, duration:', video.duration);
                        if (video.duration && video.duration > 0 && !videoDuration) {
                            videoDuration = video.duration;
                            createTimelineRuler(video.duration);
                            renderChaptersOnTimeline();
                            updateTimeDisplay();
                            
                            // Generate thumbnails if not already generated
                            setTimeout(() => {
                                if (!thumbnailGenerationInProgress && thumbnailsCache.size === 0) {
                                    generateVideoThumbnails();
                                }
                            }, 500);
                        }
                        
                        if (!wavesurfer && video.duration) {
                            setTimeout(() => {
                                if (!wavesurfer && waveformContainer && waveformContainer.offsetWidth > 0) {
                                    initializeWaveSurfer();
                                }
                            }, 500);
                        }
                    });
                    
                    // Also try on loadeddata event (but only once)
                    let loadeddataHandled = false;
                    video.addEventListener('loadeddata', () => {
                        if (loadeddataHandled) return;
                        loadeddataHandled = true;
                        
                        console.log('Video data loaded, duration:', video.duration);
                        if (video.duration && video.duration > 0 && !videoDuration) {
                            videoDuration = video.duration;
                            createTimelineRuler(video.duration);
                            renderChaptersOnTimeline();
                            updateTimeDisplay();
                            setupWaveformSelection();
                            // Generate thumbnails after video is fully loaded
                            setTimeout(() => {
                                generateVideoThumbnails();
                            }, 500);
                        }
                    });

                    // Resize handler
                    let resizeTimeout;
                    window.addEventListener('resize', () => {
                        clearTimeout(resizeTimeout);
                        resizeTimeout = setTimeout(() => {
                            if (videoDuration > 0) {
                                createTimelineRuler(videoDuration);
                                renderChaptersOnTimeline();
                            }
                        }, 250);
                    });

                    // Button handlers
                    const playPauseBtn = document.getElementById('play-pause-btn');
                    if (playPauseBtn) {
                        playPauseBtn.addEventListener('click', () => {
                            if (wavesurfer) {
                                wavesurfer.playPause();
                            } else if (video) {
                                video.paused ? video.play() : video.pause();
                            }
                        });
                    }

                    const addChapterBtn = document.getElementById('add-chapter-btn');
                    if (addChapterBtn) {
                        addChapterBtn.addEventListener('click', () => {
                            // Check for visual selection first (works without WaveSurfer)
                            const visualSelection = document.getElementById('temp-selection-visual');
                            
                            // Try to get region from WaveSurfer if available
                            let tempRegion = null;
                            if (regions) {
                                tempRegion = regions.getRegions().find(r => r.id === 'temp-selection');
                            }
                            
                            // Use currentRegion if it exists (from visual selection)
                            const selectedRegion = tempRegion || currentRegion;
                            
                            if (selectedRegion && (selectedRegion.id === 'temp-selection' || visualSelection)) {
                                const startTime = selectedRegion.start || parseFloat(visualSelection?.dataset?.start || 0);
                                const endTime = selectedRegion.end || parseFloat(visualSelection?.dataset?.end || 0);
                                
                                if (startTime >= 0 && endTime > startTime) {
                                    console.log('Creating chapter from selection:', startTime, 'to', endTime);
                                    
                                    @this.call('addChapter', startTime, endTime).then(() => {
                                        // Remove visual selection
                                        if (visualSelection) visualSelection.remove();
                                        
                                        // Remove WaveSurfer region if exists
                                        if (tempRegion) {
                                            try {
                                                tempRegion.remove();
                                            } catch (e) {
                                                console.warn('Could not remove WaveSurfer region:', e);
                                            }
                                        }
                                        
                                        currentRegion = null;
                                        updateSelectionInfo(null, null);
                                        
                                        // Refresh chapters on timeline
                                        setTimeout(() => {
                                            renderChaptersOnTimeline();
                                            
                                            // Re-add regions if WaveSurfer is working
                                            if (regions && wavesurfer) {
                                                @if($record && $record->chapters)
                                                    const chapters = @json($record->chapters);
                                                    // Clear existing regions first
                                                    regions.clearRegions();
                                                    chapters.forEach((chapter) => {
                                                        try {
                                                            regions.addRegion({
                                                                start: parseFloat(chapter.start_time),
                                                                end: parseFloat(chapter.end_time),
                                                                color: 'rgba(34, 197, 94, 0.5)',
                                                                drag: true,
                                                                resize: true,
                                                                id: 'chapter-' + chapter.id,
                                                            });
                                                        } catch (error) {
                                                            console.warn('Error re-adding region:', error);
                                                        }
                                                    });
                                                @endif
                                            }
                                        }, 500);
                                    });
                                } else {
                                    alert('Invalid selection. Please select a valid time range.');
                                }
                            } else if (selectedRegion) {
                                alert('Please select a new region on the waveform by clicking and dragging.');
                            } else {
                                alert('Please select a region on the waveform first by clicking and dragging.');
                            }
                        });
                    }
                    
                    console.log('Timeline editor initialized');
                }
            })();
        </script>
    @endpush
</x-filament-panels::page>
