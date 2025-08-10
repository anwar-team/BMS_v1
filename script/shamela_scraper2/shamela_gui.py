# -*- coding: utf-8 -*-
# shamela_gui.py (FINAL GUI: إدخال كامل + SQL + معاينة + صفحات)

import re, time
import streamlit as st
import requests
from bs4 import BeautifulSoup

from shamela_scraper import (
    BASE_URL,
    parse_book_page,
    generate_insert_sql,
    fetch_pages_for_book,
    generate_pages_insert_sql,
    insert_into_mysql,
    insert_pages_into_mysql,
    ShamelaScraperError
)

st.set_page_config(page_title="Shamela Importer GUI", page_icon="📚", layout="wide")

st.sidebar.header("⚙️ إعدادات")
preview_enabled = st.sidebar.checkbox("تفعيل المعاينة المتقدمة", value=True)
delay = st.sidebar.slider("تأخير بين الطلبات (ثانية)", 0.0, 3.0, 0.4, 0.1)

st.sidebar.subheader("🧾 خيارات الصفحات")
save_pages    = st.sidebar.checkbox("تخزين صفحات الكتاب", value=False)
pages_as_html = st.sidebar.checkbox("تخزين الصفحة كـ HTML بدل النص", value=False)

st.sidebar.subheader("🔌 اتصال MySQL للإدخال المباشر")
mysql_host = st.sidebar.text_input("Host", "127.0.0.1")
mysql_port = st.sidebar.number_input("Port", 1, 65535, 3306)
mysql_user = st.sidebar.text_input("User", "root")
mysql_pass = st.sidebar.text_input("Password", type="password")
mysql_db   = st.sidebar.text_input("Database", "bms")

st.title("📚 Shamela Importer – إدخال كامل ومعاينة")

col_l, col_r = st.columns([2,1])
with col_l:
    url_or_id = st.text_input("رابط الشاملة أو المعرّف:", placeholder="https://shamela.ws/book/43 أو 43")
with col_r:
    run_btn = st.button("تشغيل", use_container_width=True)

def _extract_id(val: str):
    val = (val or "").strip()
    if val.isdigit(): return val
    m = re.search(r"/book/(\d+)", val)
    return m.group(1) if m else None

def _fetch_page_html(book_id: str, page_no: int) -> str:
    url = f"{BASE_URL}/book/{book_id}/{page_no}"
    r = requests.get(url, timeout=30)
    r.raise_for_status()
    soup = BeautifulSoup(r.text, "html.parser")
    main = soup.find(id="book") or soup.find("div", {"class": "container"}) or soup
    return main.decode()

if run_btn:
    bid = _extract_id(url_or_id)
    if not bid:
        st.error("📛 المعرّف غير صالح")
    else:
        try:
            with st.spinner("🔄 جاري التحليل..."):
                bk = parse_book_page(bid)

            # حماية توافقية
            if not hasattr(bk, "volumes") or bk.volumes is None:
                bk.volumes = []
            if not hasattr(bk, "volume_count") or bk.volume_count is None:
                bk.volume_count = len(bk.volumes) if bk.volumes else 1

            st.success("✅ تم استخراج بيانات الكتاب")
            meta1, meta2 = st.columns(2)
            with meta1:
                st.write(f"**العنوان:** {bk.title}")
                st.write(f"**المعرّف:** {bk.shamela_id}")
                if bk.page_count: st.write(f"**الصفحات:** {bk.page_count}")
                count = bk.volume_count or (len(bk.volumes) if bk.volumes else 1)
                st.write(f"**الأجزاء:** {count}")
                if bk.categories: st.write("**التصنيفات:** " + "، ".join(bk.categories))
            with meta2:
                if bk.publisher: st.write(f"**الناشر:** {bk.publisher}")
                if bk.edition: st.write(f"**الطبعة:** {bk.edition}")
                if bk.publication_year: st.write(f"**سنة النشر:** {bk.publication_year}")
                st.write("**الأجزاء (نطاق الصفحات):**")
                if bk.volumes:
                    for v in bk.volumes:
                        st.write(f"• {v.number}: {v.title} [{v.page_start or '?'} → {v.page_end or '?'}]")
                else:
                    st.info("لم تُكتشف قائمة الأجزاء من صفحة 1؛ تم الاعتماد على بطاقة الكتاب.")

            tabs = st.tabs([
                "🧾 SQL (الكتاب/الفهرس)",
                "🗄️ إدخال MySQL مباشر",
                "🧾 SQL (الصفحات)",
                "👁️‍🗨️ معاينة"
            ])

            # SQL للكتاب/الفهرس
            with tabs[0]:
                sql_script = generate_insert_sql(bk)
                st.code(sql_script, language="sql")
                st.download_button("تحميل SQL (الكتاب/الفهرس)", data=sql_script.encode("utf-8"),
                                   file_name=f"book_{bk.shamela_id}.sql", mime="text/sql")

            # إدخال مباشر
            with tabs[1]:
                st.caption("الأول: إدخال الكتاب/الفهرس. ثانيًا (اختياري): الصفحات.")
                if st.button("💾 إدخال الكتاب/الفهرس الآن"):
                    try:
                        sql_used = insert_into_mysql(bk, {
                            "host": mysql_host, "port": mysql_port,
                            "user": mysql_user, "password": mysql_pass,
                            "database": mysql_db
                        })
                        st.success("تم إدخال الكتاب/الفهرس بنجاح ✅")
                        with st.expander("عرض السكربت الذي تم تنفيذه"):
                            st.code(sql_used, language="sql")
                    except Exception as e:
                        st.error(f"فشل إدخال الكتاب: {e}")

                if save_pages:
                    st.markdown("---")
                    st.caption("إدخال صفحات الكتاب (قد يستغرق وقتًا)")
                    if st.button("💾 إدخال الصفحات الآن"):
                        try:
                            pages = fetch_pages_for_book(bk, as_html=pages_as_html)
                            sql_used = insert_pages_into_mysql(bk, pages, {
                                "host": mysql_host, "port": mysql_port,
                                "user": mysql_user, "password": mysql_pass,
                                "database": mysql_db
                            })
                            st.success(f"تم إدخال {len(pages)} صفحة ✅")
                            with st.expander("عرض سكربت الصفحات الذي تم تنفيذه"):
                                st.code(sql_used, language="sql")
                        except Exception as e:
                            st.error(f"فشل إدخال الصفحات: {e}")
                else:
                    st.info("لتفعيل إدخال الصفحات، فعّل خيار (تخزين صفحات الكتاب) من الشريط الجانبي.")

            # SQL للصفحات فقط
            with tabs[2]:
                st.caption("توليد سكربت INSERT لكل صفحات الكتاب (لتنفيذ يدوي).")
                if st.button("تجهيز SQL للصفحات"):
                    pages = fetch_pages_for_book(bk, as_html=pages_as_html)
                    pages_sql = generate_pages_insert_sql(bk, pages)
                    st.code(pages_sql, language="sql")
                    st.download_button("تحميل SQL (الصفحات)", data=pages_sql.encode("utf-8"),
                                       file_name=f"book_{bk.shamela_id}_pages.sql", mime="text/sql")

            # المعاينة
            with tabs[3]:
                if not preview_enabled:
                    st.info("المعاينة متوقفة من الشريط الجانبي.")
                else:
                    left, right = st.columns([1.2, 2.8])
                    with left:
                        st.caption("فهرس الموضوعات")
                        def draw(nodes, lvl=0):
                            for ch in nodes:
                                cols = st.columns([0.8, 0.2])
                                with cols[0]:
                                    st.write(("　"*lvl) + "• " + ch.title)
                                with cols[1]:
                                    label = f"ص {ch.page_number or '-'}"
                                    if st.button(label, key=f"{ch.title}_{lvl}_{ch.page_number}"):
                                        st.session_state["__sel_page__"] = ch.page_number
                                if ch.children:
                                    draw(ch.children, lvl+1)
                        draw(bk.index)

                    with right:
                        st.caption("نص الصفحة")
                        default_page = 1
                        if bk.volumes and getattr(bk.volumes[0], "page_start", None):
                            default_page = bk.volumes[0].page_start
                        sel = st.session_state.get("__sel_page__", default_page)
                        if sel:
                            try:
                                html = _fetch_page_html(bk.shamela_id, int(sel))
                                st.markdown(html, unsafe_allow_html=True)
                            except Exception as e:
                                st.warning(f"تعذّر عرض الصفحة {sel}: {e}")

                        nav1, nav2, nav3 = st.columns(3)
                        with nav1:
                            if st.button("⬅️ السابق"):
                                st.session_state["__sel_page__"] = max(1, int(sel or 1) - 1)
                        with nav2:
                            page_in = st.text_input("اذهب إلى صفحة:", value=str(sel or 1))
                            if st.button("اذهب"):
                                try:
                                    st.session_state["__sel_page__"] = max(1, int(page_in))
                                except:
                                    st.error("رقم صفحة غير صالح")
                        with nav3:
                            if st.button("التالي ➡️"):
                                st.session_state["__sel_page__"] = (int(sel or 1) + 1)

            time.sleep(delay)

        except ShamelaScraperError as e:
            st.error(f"خطأ استخراج: {e}")
        except Exception as e:
            st.error(f"خطأ غير متوقع: {e}")
