<x-filament-panels::page>
    {{-- Page content --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

    <div style="display:flex;flex-direction:column;gap:12px;max-width:480px">

        <!-- Viewport -->
        <div style="position:relative;width:100%;aspect-ratio:4/3;background:#000;border-radius:8px;overflow:hidden">
            <div id="isbn-interactive" style="width:100%;height:100%"></div>

            <!-- Scan line reticle -->
            <div id="isbn-reticle" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;z-index:10">
                <div style="width:75%;height:35%;position:relative;outline:2px dashed rgba(255,255,255,0.4);border-radius:2px">
                    <div id="isbn-scanline" style="position:absolute;left:0;right:0;height:2px;background:rgba(255,255,255,0.7);top:0;animation:isbn-scan 2s ease-in-out infinite"></div>
                </div>
            </div>

            <!-- Start overlay -->
            <div id="isbn-overlay" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;background:rgba(0,0,0,0.7);z-index:20;padding:20px;text-align:center">
                <x-filament::button onclick="isbnStart()">Start Camera</x-filament::button>
            </div>

            <!-- Error overlay -->
            <div id="isbn-error" style="display:none;position:absolute;inset:0;align-items:center;justify-content:center;background:rgba(0,0,0,0.8);z-index:20;padding:20px;text-align:center">
                <p style="color:#f87171;font-size:0.85rem">Camera access denied. Please allow camera permissions and reload.</p>
            </div>
        </div>

        <!-- Controls -->
        <div style="display:flex;gap:8px;align-items:center">
            <span id="isbn-status" style="font-size:0.8rem;color:#6b7280">Idle</span>
            <x-filament::button id="isbn-stop-btn" onclick="isbnStop()" color="danger" class="ml-auto" style="display:none">Stop</x-filament::button>
        </div>

        <!-- Manual entry -->
        <div style="display:flex;gap:8px">
            <input id="isbn-manual" type="text" placeholder="Or enter ISBN manually…" maxlength="17"
                   oninput="this.value=this.value.replace(/[^0-9X-]/gi,'')"
                   onkeydown="if(event.key==='Enter')isbnManual()"
                   style="flex:1;padding:8px 12px;border-radius:6px;border:1px solid #d1d5db;font-size:0.85rem" />
            <x-filament::button onclick="isbnManual()">Go</x-filament::button>
        </div>
    </div>

    <style>
        @keyframes isbn-scan {
            0%,100% { top:5% }
            50%      { top:95% }
        }
        #isbn-interactive video { width:100%;height:100%;object-fit:cover;display:block }
        #isbn-interactive canvas.drawingBuffer { position:absolute;top:0;left:0;width:100%!important;height:100%!important }
    </style>

    <script>
        (function(){
            const BASE_URL = '{{config('app.url')}}/admin/books/create/';
            let scanning = false, lastCode = null, debounceTimer = null;

            function setStatus(msg){ document.getElementById('isbn-status').textContent = msg }
            function showToast(msg){ /* hook into Filament's notification system if available, else alert */
                if(window.Livewire){ Livewire.dispatch('notify', {title: msg}) } else { alert(msg) }
            }

            window.isbnStart = function(){
                document.getElementById('isbn-overlay').style.display = 'none';
                document.getElementById('isbn-stop-btn').style.display = '';
                setStatus('Starting…');

                Quagga.init({
                    inputStream: { name:'Live', type:'LiveStream', target: document.querySelector('#isbn-interactive'),
                        constraints: { facingMode:'environment', width:{ideal:1280}, height:{ideal:960} } },
                    decoder: { readers:['ean_reader','ean_8_reader','upc_reader','upc_e_reader'], multiple:false },
                    locate:true, numOfWorkers:2, frequency:10,
                }, function(err){
                    if(err){ document.getElementById('isbn-error').style.display='flex'; setStatus('Error'); return }
                    Quagga.start(); scanning=true; setStatus('Scanning…');
                });

                Quagga.onDetected(function(result){
                    const code = result.codeResult.code;
                    if(!code || code===lastCode) return;
                    const errors = result.codeResult.decodedCodes.filter(c=>c.error!==undefined).map(c=>c.error);
                    const avgErr = errors.reduce((a,b)=>a+b,0)/(errors.length||1);
                    if(avgErr>0.5) return;
                    const clean = code.replace(/[-\s]/g,'').trim();
                    if(clean.length!==13 && clean.length!==10) return;
                    if(clean.length===13){
                        if(!clean.startsWith('978')&&!clean.startsWith('979')) return;
                        let chk=0; for(let i=0;i<13;i++) chk+=(i%2===0)?+clean[i]:3*+clean[i];
                        if(chk%10!==0) return;
                    } else {
                        let chk=0; for(let i=0;i<9;i++) chk+=(10-i)*+clean[i];
                        const t=clean[9]; chk+=(t==='x'||t==='X')?10:+t;
                        if(chk%11!==0) return;
                    }
                    clearTimeout(debounceTimer);
                    debounceTimer=setTimeout(()=>{ lastCode=code; isbnStop(); isbnRedirect(code); },200);
                });
            };

            window.isbnStop = function(){
                if(!scanning) return;
                Quagga.stop(); scanning=false; setStatus('Idle');
                document.getElementById('isbn-stop-btn').style.display='none';
                document.getElementById('isbn-overlay').style.display='flex';
                lastCode=null;
            };

            window.isbnManual = function(){
                const val = document.getElementById('isbn-manual').value.trim();
                if(!val) return;
                const digits = val.replace(/[-\s]/g,'');
                if(digits.length!==10&&digits.length!==13){ showToast('ISBN must be 10 or 13 digits'); return; }
                isbnRedirect(val);
            };

            function isbnRedirect(isbn){
                const clean = isbn.replace(/[-\s]/g,'').trim();
                setStatus('Redirecting to '+clean+'…');
                setTimeout(()=>{ window.location.href = BASE_URL + encodeURIComponent(clean); }, 400);
            }
        })();
    </script>
</x-filament-panels::page>
