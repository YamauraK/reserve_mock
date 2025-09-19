@extends('layouts.app')

@section('title','新規予約')

@section('content')
    <form method="post" action="{{ route('reservations.store') }}" class="form-card space-y-6">
        @csrf

        {{-- セクション：基本情報 --}}
        <div class="form-section">
            <div class="form-section-title">基本情報</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="field">
                <label class="label">企画 <span class="required">必須</span></label>
                <select name="campaign_id" class="select" required>
                    @foreach($campaigns as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="label">店舗 <span class="required">必須</span></label>
                <select name="store_id" class="select" required>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" @selected($defaultStoreId==$s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="label">受付チャネル</label>
                <select name="channel" class="select">
                    <option value="store">店頭</option>
                    <option value="tokushimaru">とくし丸</option>
                </select>
            </div>
        </div>

        {{-- セクション：お客様情報 --}}
        <div class="form-section">
            <div class="form-section-title">お客様情報</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="field">
                <label class="label">氏名 <span class="required">必須</span></label>
                <input name="customer_name" class="input" required />
            </div>
            <div class="field">
                <label class="label">フリガナ</label>
                <input name="customer_kana" class="input" />
            </div>
            <div class="field">
                <label class="label">電話 <span class="required">必須</span></label>
                <input name="phone" class="input" required />
            </div>
            <div class="field">
                <label class="label">郵便番号</label>
                <input name="zip" class="input" placeholder="例: 1000001" />
                <div class="help">ハイフンなし7桁</div>
            </div>
            <div class="field md:col-span-2">
                <label class="label">住所</label>
                <input name="address1" class="input" />
                <input name="address2" class="input mt-2" placeholder="建物名・部屋番号など" />
            </div>
        </div>

        {{-- セクション：受取 --}}
        <div class="form-section">
            <div class="form-section-title">受取</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="field">
                <label class="label">受取日</label>
                <input type="date" name="pickup_date" class="input" />
            </div>
            <div class="field">
                <label class="label">受取時間帯</label>
                <input name="pickup_time_slot" class="input" placeholder="例: 10:00-12:00" />
            </div>
        </div>

        {{-- セクション：商品 --}}
        <div class="form-section">
            <div class="form-section-title">商品</div>
        </div>

        <div class="product-list">
            @foreach($products as $p)
                <div class="product-row">
                    <div class="product-name">
                        <div class="font-semibold">{{ $p->name }}</div>
                        <div class="text-sm text-gray-500">¥{{ number_format($p->price) }}</div>
                    </div>
                    <div class="product-qty">
                        <label class="text-sm text-gray-500">数量</label>
                        <input type="number" name="items[{{ $loop->index }}][quantity]" min="0" value="0"
                               class="input qty-input"
                               data-price="{{ $p->price }}"
                               oninput="window.calcTotal && window.calcTotal()">
                        <input type="hidden" name="items[{{ $loop->index }}][product_id]" value="{{ $p->id }}" />
                    </div>
                </div>
                <div class="text-xs text-emerald-700 mt-1 hidden disc-row" data-product="{{ $p->id }}">
                    <span data-role="disc-label">早割</span>：-¥<span data-role="disc-line">0</span>
                </div>
            @endforeach
        </div>

        {{-- 合計（任意） --}}
        <div class="total-box">
            <div class="total-label">概算合計（税込想定）</div>
            <div id="jsTotal" class="total-value">¥0</div>
        </div>

        {{-- 早割サマリ（プレビュー） --}}
        <div class="total-box mt-2">
            <div class="total-label">早割見込み（自動適用）</div>
            <div class="total-value text-emerald-700">-¥<span id="jsDiscountTotal">0</span></div>
            <div class="total-note text-xs text-slate-500" id="jsDiscountNote"></div>
        </div>

        {{-- 割引後概算合計（プレビュー） --}}
        <div class="total-box mt-2">
            <div class="total-label">割引後概算合計</div>
            <div class="total-value font-bold">¥<span id="jsFinalTotal">0</span></div>
        </div>

        {{-- 備考 --}}
        <div class="field">
            <label class="label">備考</label>
            <textarea name="notes" class="textarea" rows="3"></textarea>
        </div>

        {{-- アクション --}}
        <div class="form-actions">
            <a href="{{ route('reservations.index') }}" class="btn">戻る</a>
            <button class="btn btn-primary">登録</button>
        </div>
    </form>

    {{-- ＊任意：合計の自動計算（CSS目的の画面演出。削除可） --}}
    <script>
        window.calcTotal = function(){
            let total = 0;
            document.querySelectorAll('.qty-input').forEach(el=>{
                const q = parseInt(el.value || '0',10);
                const price = parseInt(el.dataset.price || '0',10);
                if(q>0) total += q*price;
            });
            const t = document.getElementById('jsTotal');
            if(t){ t.textContent = '¥' + total.toLocaleString(); }
        };
        window.calcTotal();

        (function(){
            const form = document.querySelector('form[action="{{ route('reservations.store') }}"]');
            if(!form) return;

            const selCampaign = form.querySelector('select[name="campaign_id"]');
            const selStore    = form.querySelector('select[name="store_id"]');
            const selChannel  = form.querySelector('select[name="channel"]');
            const qtyInputs   = form.querySelectorAll('.qty-input');

            const discTotalEl = document.getElementById('jsDiscountTotal');
            const finalTotalEl= document.getElementById('jsFinalTotal');
            const noteEl      = document.getElementById('jsDiscountNote');

            async function preview(){
                // 入力が揃っていなければリセット
                if(!selCampaign.value || !selStore.value){
                    return resetUI();
                }
                // items（数量>0のみ）を収集
                const items = [];
                qtyInputs.forEach((el, idx) => {
                    const q = parseInt(el.value || '0', 10);
                    if(q > 0){
                        const hidden = form.querySelector(`input[name="items[${idx}][product_id]"]`);
                        if(hidden) items.push({ product_id: Number(hidden.value), quantity: q });
                    }
                });
                if(items.length === 0){ return resetUI(); }

                try{
                    const res = await fetch('{{ route('reservations.price-preview') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            campaign_id: selCampaign.value,
                            store_id:    selStore.value,
                            channel:     selChannel?.value || 'store',
                            items
                        })
                    });
                    if(!res.ok) throw new Error('preview failed');
                    const data = await res.json();
                    updateUI(data);
                }catch(e){
                    resetUI();
                    console.warn(e);
                }
            }

            function resetUI(){
                // 行バッジを消す
                document.querySelectorAll('.disc-row').forEach(b=>{
                    b.classList.add('hidden');
                    b.querySelector('[data-role="disc-line"]').textContent = '0';
                    b.querySelector('[data-role="disc-label"]').textContent = '早割';
                });
                // サマリをゼロに
                if(discTotalEl) discTotalEl.textContent = '0';
                if(finalTotalEl) finalTotalEl.textContent = '0';
                if(noteEl) noteEl.textContent = '';
            }

            function updateUI(data){
                resetUI(); // 一旦クリアしてから反映

                const uniqNotes = new Set();

                (data.lines || []).forEach(line => {
                    // 対応する product-row を探してバッジを更新
                    const row = document.querySelector(`.product-row input[type="hidden"][value="${line.product_id}"]`)?.closest('.product-row');
                    const badge = row?.querySelector('.disc-row');
                    if(!badge) return;

                    const lineDisc = Number(line.line_discount || 0);
                    if(lineDisc > 0){
                        badge.classList.remove('hidden');
                        const labelEl = badge.querySelector('[data-role="disc-label"]');
                        const amtEl   = badge.querySelector('[data-role="disc-line"]');
                        if(labelEl) labelEl.textContent = line.rule?.display || '早割';
                        if(amtEl)   amtEl.textContent   = lineDisc.toLocaleString();
                        if(line.rule?.display && line.rule?.cutoff_date){
                            uniqNotes.add(`${line.rule.display}（〜${line.rule.cutoff_date}）`);
                        }
                    }
                });

                // サマリ
                if(discTotalEl) discTotalEl.textContent = Number(data?.totals?.discount || 0).toLocaleString();
                if(finalTotalEl)finalTotalEl.textContent= Number(data?.totals?.final    || 0).toLocaleString();
                if(noteEl) noteEl.textContent = Array.from(uniqNotes).join(' / ');
            }

            // イベント登録（既存の calcTotal と共存）
            ['change','input'].forEach(ev=>{
                selCampaign.addEventListener(ev, preview);
                selStore.addEventListener(ev, preview);
                if(selChannel) selChannel.addEventListener(ev, preview);
                qtyInputs.forEach(el => el.addEventListener(ev, preview));
            });

            // 初期計算
            preview();
        })();
    </script>
@endsection
