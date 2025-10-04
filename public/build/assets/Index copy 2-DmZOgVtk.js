import{D as Y,Q as nt,d as p,T as it,s as B,p as ct,c as R,w as m,o as n,f as K,a as e,b as g,g as A,j as S,x as bt,e as b,h as f,t as a,F as h,i as pt,v as z,u as T,n as ut}from"./app-PtKpDxL3.js";import{_ as gt,a as yt}from"./Update-x6-ww5S5.js";import{_ as E}from"./Modal.vue_vue_type_style_index_0_scoped_fd08cd24_lang-DMiA-fcE.js";import{_ as vt}from"./TransparentButton-3KD1yN4Q.js";import{S as mt}from"./SearchColored-CyqFvcJS.js";import{B as _t}from"./Back-BgKFSBGh.js";import{_ as xt}from"./AdminPanel-Dy3tTlJm.js";import"./Modal-B0NwKHOQ.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./InputError-dzOUU8D2.js";import"./FormComponent-eogPIn0L.js";import"./RetailGroup-C1sh5lal.js";import"./Logout-ho77fkiB.js";/* empty css                                                             */import"./RetailItems-ozaKlTSo.js";import"./Attendance-CnJEetSN.js";const ft={class:"absolute adjust"},ht={class:"flex justify-start items-center"},Tt=["value"],Et=["value"],Ct={"date-rangepicker":"",class:"flex items-center"},It={class:"relative ml-5"},wt=["placeholder"],At={role:"tablist",class:"tabs tabs-lifted mt-10 p-5"},Dt={role:"tabpanel",class:"tab-content bg-base-200 border-base-300 p-6 h-[70vh] overflow-y-auto"},Nt={class:"container mx-auto px-4"},Ot={class:"flex flex-wrap -mx-4"},Rt={key:0,class:"col-span-full text-center mt-8"},St={key:1,class:"col-span-full text-center mt-8"},Lt={class:"text-red-600 text-lg"},kt={key:2,class:"col-span-full text-center mt-8"},Ut={class:"bg-blue-600 text-white text-center py-1 font-semibold"},Pt={class:"bg-blue-400 text-white text-center py-1"},$t={class:"w-full px-4 mb-8"},Mt={class:"flex bg-gray-200 font-semibold"},Gt={class:"w-1/4 p-2 text-center border-r border-gray-400"},Vt={class:"divide-y divide-gray-300"},jt={class:"w-1/2 p-2 border-r border-gray-300"},Ht={class:"w-1/4 p-2 text-center border-r border-gray-300"},Ft={class:"w-1/4 p-2 text-center"},Yt=["onUpdate:modelValue","onInput","disabled","title"],Bt={class:"flex bg-red-200"},Kt={class:"w-1/4 p-2 text-center border-r border-gray-300"},zt={class:"w-1/4 p-2 text-center"},Jt={class:"max-w-md mx-auto border border-gray-300"},Wt={class:"w-full"},qt={class:"border-b border-l border-gray-300 p-2 text-sm text-right"},Qt={class:"border-l border-gray-300 p-2 text-sm text-right"},Xt={role:"tabpanel",class:"tab-content bg-base-100 border-base-200 p-6 h-[85vh] overflow-y-auto"},Zt={key:0,class:"col-span-full text-center mt-8"},te={key:1,class:"col-span-full text-center mt-8"},ee={class:"text-red-600 text-lg"},re={key:2,class:"col-span-full text-center mt-8"},oe={class:"flex justify-between mb-4"},se={class:"w-full border-collapse border border-gray-300"},le={class:"border border-gray-300 p-2"},de={class:"border border-gray-300 p-2 text-center"},ae={class:"border border-gray-300 p-2 text-center"},ne={class:"border border-gray-300 p-2 text-right"},ie={class:"border border-gray-300 p-2 text-right"},ce={class:"bg-gray-200 font-bold"},be={class:"border border-gray-300 p-2 text-center"},pe={class:"border border-gray-300 p-2 text-center"},ue={class:"border border-gray-300 p-2 text-right"},ge={class:"border border-gray-300 p-2 text-right"},Se={__name:"Index copy 2",setup(ye){Y.defaults.headers.common["X-CSRF-TOKEN"]=document.querySelector('meta[name="csrf-token"]').getAttribute("content");const L=nt(),_=p(Object.entries(L.props.groupedPicklist).reduce((r,[t,s])=>(r[t]=s.map(o=>({...o,actual:o.ACTUAL})),r),{})),D=p(Object.entries(L.props.groupedPicklist).reduce((r,[t,s])=>(r[t]=s.map(o=>({...o,actual:o.ACTUAL})),r),{})),k=r=>new Date(r).toLocaleDateString(),y=r=>new Intl.NumberFormat("en-PH",{style:"currency",currency:"PHP"}).format(r),U=r=>r.reduce((t,s)=>t+Number(s.CHECKINGCOUNT||0),0),P=r=>r.reduce((t,s)=>t+Number(s.CHECKINGCOUNT||0),0),$=r=>r.reduce((t,s)=>t+Number(s.COST||0),0),M=r=>r.reduce((t,s)=>t+Number(s.COST||0)*Number(s.CHECKINGCOUNT||0),0),J=p(""),W=p(""),q=p(""),G=p(!1),V=p(!1),j=p(!1),C=p(null),v=it({StartDate:"2024-07-22",StoreName:"Urdaneta2"}),H=()=>{v.get(route("picklist.getrange"),{preserveScroll:!0})};p(null);const N=B(()=>{const r=v.StartDate;if(r){const t=new Date(r),s=t.getFullYear(),o=String(t.getMonth()+1).padStart(2,"0"),d=String(t.getDate()).padStart(2,"0");return`${s}-${o}-${d}`}return""});p(null),B(()=>{const r=v.EndDate;if(r){const t=new Date(r),s=t.getFullYear(),o=String(t.getMonth()+1).padStart(2,"0"),d=String(t.getDate()).padStart(2,"0");return`${s}-${o}-${d}`}return""});const Q=()=>{G.value=!1},X=()=>{V.value=!1},Z=r=>r.reduce((t,s)=>t+parseFloat(s.COUNTED||0),0),I=(r,t)=>r.reduce((s,o)=>s+(o[t]||0),0),tt=r=>r.reduce((t,s)=>t+parseFloat(s.actual||0),0),u=()=>{const r=new Date;return`${r.getMonth()+1}/${r.getDate()}/${r.getFullYear().toString().substr(-2)}`},i=r=>{const t=parseFloat(r);return Number.isInteger(t)?t.toString():Math.round(t).toString()},et=async(r,t,s,o)=>{try{const l=_.value[r].find(x=>x.ITEMID===s);if(!l){console.error("Item not found");return}if(!l.JOURNALID){console.error("JOURNALID is missing for this item");return}const c=await Y.post("/api/update-actual",{journal_id:l.JOURNALID,store_name:r,item_name:t,item_id:s,actual:o});c.data.success?l.ACTUAL=o:console.error("Failed to update ACTUAL value",c.data)}catch(d){d.response&&d.response.data?(console.error("Server validation errors:",d.response.data.errors),Object.entries(d.response.data.errors).forEach(([l,c])=>{console.error(`${l}: ${c.join(", ")}`)})):console.error("Error updating ACTUAL value:",d.message)}},rt=p(null),ot=()=>{window.location.href="/picklist"},st=()=>{window.location.href="/mgcount"},lt=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),t=Object.entries(_.value);let s="";for(let o=0;o<t.length;o+=2){const d=t.slice(o,o+2).map(([l,c])=>{const x=c.map(w=>`
        <tr>
          <td class="border p-1">${w.ITEMNAME}</td>
          <td class="border p-1 text-center">${i(w.COUNTED)}</td>
          <td class="border p-1 text-center"></td>
        </tr>
      `).join(""),O=I(c,"COUNTED");return I(c,"ACTUAL"),`
        <div class="store-section">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST - ${l}</div>
          <div class="bg-blue-400">DELIVERY DATE: ${u()}</div>
          <table>
            <thead>
              <tr>
                <th class="border p-1">PRODUCT</th>
                <th class="border p-1">${l}</th>
                <th class="border p-1">ACTUAL</th>
              </tr>
            </thead>
            <tbody>
              ${x}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${i(O)}</td>
                <td class="border p-1 text-center font-bold"></td>
              </tr>
            </tbody>
          </table>
          <table style="margin-top: 5px;">
            <tbody>
              <tr>
                <td class="text-red font-semibold" style="width: 20%;">DISPATCHER:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">${u()}</td>
              </tr>
              <tr>
                <td class="font-semibold" style="width: 20%;">LOGISTICS:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">${u()}</td>
              </tr>
            </tbody>
          </table>
        </div>
      `}).join("");s+=`
      <div class="page-container">
        ${d}
      </div>
    `}r.document.write(`
    <html>
      <head>
        <title>Packing List</title>
        <style>
          @page {
            size: legal portrait;
            margin: 0;
          }
          body { 
            font-family: Arial, sans-serif; 
            font-size: 14px;
            margin: 0;
            padding: 0;
          }
          .page-container {
            width: 100%;
            height: 100vh;
            page-break-after: always;
            padding: 10mm;
            box-sizing: border-box;
            display: flex;
            justify-content: space-between;
          }
          .store-section { 
            width: 48%; 
            max-width: 48%; 
          }
          table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5mm;
          }
          th, td { 
            border: 1px solid black; 
            padding: 2px; 
            font-size: 14px; 
          }
          .text-center { text-align: center; }
          .bg-blue-800 { background-color: #2b6cb0; color: white; text-align: center; padding: 3px 0; font-weight: bold; }
          .bg-blue-600 { background-color: #3182ce; color: white; text-align: center; padding: 2px 0; font-weight: 600; }
          .bg-blue-400 { background-color: #4299e1; color: white; text-align: center; padding: 2px 0; }
          .bg-red-200 { background-color: #fed7d7; }
          .signature-line { border-bottom: 1px solid #ccc; margin-top: 5px; }
          .text-right { text-align: right; }
          .text-red { color: red; }
          .font-semibold { font-weight: 600; }
          .font-bold { font-weight: bold; }
        </style>
      </head>
      <body>
        ${s}
      </body>
    </html>
  `),r.document.close(),r.focus(),r.print(),r.close()},dt=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),t=Object.entries(_.value);let s="";for(let o=0;o<t.length;o+=2){const d=t.slice(o,o+2).map(([l,c])=>{const x=c.map(F=>`
        <tr>
          <td class="border p-1">${F.ITEMNAME}</td>
          <td class="border p-1 text-center">${i(F.COUNTED)}</td>
          <td class="border p-1 text-center">0</td>
        </tr>
      `).join(""),O=I(c,"COUNTED"),w=I(c,"ACTUAL");return`
        <div class="store-section">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST - ${l}</div>
          <div class="bg-blue-400">DELIVERY DATE: ${u()}</div>
          <table>
            <thead>
              <tr>
                <th class="border p-1">PRODUCT</th>
                <th class="border p-1">${l}</th>
                <th class="border p-1">ACTUAL</th>
              </tr>
            </thead>
            <tbody>
              ${x}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${i(O)}</td>
                <td class="border p-1 text-center font-bold">${i(w)}</td>
              </tr>
            </tbody>
          </table>
          <table style="margin-top: 5px;">
            <tbody>
              <tr>
                <td class="text-red font-semibold" style="width: 20%;">DISPATCHER:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">${u()}</td>
              </tr>
              <tr>
                <td class="font-semibold" style="width: 20%;">LOGISTICS:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">${u()}</td>
              </tr>
            </tbody>
          </table>
        </div>
      `}).join("");s+=`
      <div class="page-container">
        ${d}
      </div>
    `}r.document.write(`
    <html>
      <head>
        <title>Packing List</title>
        <style>
          @page {
            size: legal portrait;
            margin: 0;
          }
          body { 
            font-family: Arial, sans-serif; 
            font-size: 14px;
            margin: 0;
            padding: 0;
          }
          .page-container {
            width: 100%;
            height: 100vh;
            page-break-after: always;
            padding: 10mm;
            box-sizing: border-box;
            display: flex;
            justify-content: space-between;
          }
          .store-section { 
            width: 48%; 
            max-width: 48%; 
          }
          table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5mm;
          }
          th, td { 
            border: 1px solid black; 
            padding: 2px; 
            font-size: 14px; 
          }
          .text-center { text-align: center; }
          .bg-blue-800 { background-color: #2b6cb0; color: white; text-align: center; padding: 3px 0; font-weight: bold; }
          .bg-blue-600 { background-color: #3182ce; color: white; text-align: center; padding: 2px 0; font-weight: 600; }
          .bg-blue-400 { background-color: #4299e1; color: white; text-align: center; padding: 2px 0; }
          .bg-red-200 { background-color: #fed7d7; }
          .signature-line { border-bottom: 1px solid #ccc; margin-top: 5px; }
          .text-right { text-align: right; }
          .text-red { color: red; }
          .font-semibold { font-weight: 600; }
          .font-bold { font-weight: bold; }
        </style>
      </head>
      <body>
        ${s}
      </body>
    </html>
  `),r.document.close(),r.focus(),r.print(),r.close()},at=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0");let t="";for(const[s,o]of Object.entries(_.value))t+=`
      <div class="receipt-page">
        <div class="text-center mb-4">
          <h3 class="font-bold">ELIIN CORPORATION</h3>
          <p>MALIWALO</p>
          <p>TARLAC CITY</p>
          <h3 class="font-bold">DELIVERY GOODS RECEIPT: BW PRODUCTS</h3>
        </div>
        
        <div class="flex justify-between mb-4">
          <div>
            <p>DR #: ${o[0].JOURNALID}</p> 
            <p>DELIVERY DATE: ${k(o[0].POSTEDDATETIME)}</p>
          </div>
          <div>
            <p>RECIEVED FROM:</p>
            <p>HEADOFFICE</p>
          </div>
          <div>
            <p>DELIVERED TO:</p>
            <p>${s}</p>
          </div>
        </div>
        
        <table class="w-full border-collapse border border-gray-300">
          <thead>
            <tr class="bg-gray-200">
              <th class="border border-gray-300 p-2">PRODUCT DESCRIPTION</th>
              <th class="border border-gray-300 p-2">TARGET</th>
              <th class="border border-gray-300 p-2">ALLOC</th>
              <th class="border border-gray-300 p-2">TOTAL</th>
              <th class="border border-gray-300 p-2">RECEIVE QUANTITY</th>
              <th class="border border-gray-300 p-2">TRANSFER COST</th>
              <th class="border border-gray-300 p-2">TOTAL AMOUNT</th>
            </tr>
          </thead>
          <tbody>
            ${o.map(d=>`
              <tr>
                <td class="border border-gray-300 p-2">${d.ITEMNAME}</td>
                <td class="border border-gray-300 p-2 text-center">${i(d.CHECKINGCOUNT)}</td>
                <td class="border border-gray-300 p-2 text-center">0</td>
                <td class="border border-gray-300 p-2 text-center">${i(Number(d.CHECKINGCOUNT))}</td>
                <td class="border border-gray-300 p-2 text-center"></td>
                <td class="border border-gray-300 p-2 text-right">${y(d.COST)}</td>
                <td class="border border-gray-300 p-2 text-right">${y(Number(d.COST)*Number(d.CHECKINGCOUNT))}</td>
              </tr>
            `).join("")}
            <tr class="bg-gray-200 font-bold">
              <td class="border border-gray-300 p-2">TOTAL</td>
              <td class="border border-gray-300 p-2 text-center">${i(P(o))}</td>
              <td class="border border-gray-300 p-2 text-center">0</td>
              <td class="border border-gray-300 p-2 text-center">${i(U(o))}</td>
              <td class="border border-gray-300 p-2 text-center"></td>
              <td class="border border-gray-300 p-2 text-right">${y($(o))}</td>
              <td class="border border-gray-300 p-2 text-right">${y(M(o))}</td>
            </tr>
          </tbody>
        </table>
        
        <div class="mt-8 flex justify-between">
          <div>
            <p>ENDORSED BY: DISPATCHING</p>
            <p>_____________________________</p>
            <p>NAME & SIGNATURE / DATE</p>
          </div>
          <div>
            <p>RECEIVED BY STORE</p>
            <p>_____________________________</p>
            <p>NAME & SIGNATURE / DATE</p>
          </div>
        </div>
      </div>
      <div class="page-break"></div>
    `;r.document.write(`
    <html>
      <head>
        <title>Delivery Goods Receipt</title>
        <style>
          @page {
            size: A4 portrait;
            margin: 1cm;
          }
          body { 
            font-family: Arial, sans-serif; 
            font-size: 14px;
            margin: 0;
            padding: 0;
          }
          .receipt-page {
            width: 100%;
            height: 100%;
            page-break-after: always;
          }
          table { 
            width: 100%; 
            border-collapse: collapse; 
          }
          th, td { 
            border: 1px solid black; 
            padding: 4px; 
            font-size: 14px; 
          }
          .text-center { text-align: center; }
          .text-right { text-align: right; }
          .font-bold { font-weight: bold; }
          .mb-4 { margin-bottom: 16px; }
          .mt-8 { margin-top: 32px; }
          .flex { display: flex; }
          .justify-between { justify-content: space-between; }
          .bg-gray-200 { background-color: #edf2f7; }
          .page-break { page-break-after: always; }
        </style>
      </head>
      <body>
        ${t}
      </body>
    </html>
  `),r.document.close(),r.focus(),r.print(),r.close()};return(r,t)=>{const s=ct("InputError");return n(),R(xt,{"active-tab":"PICKLIST"},{modals:m(()=>[V.value?(n(),R(gt,{key:0,onToggleActive:X})):K("",!0),G.value?(n(),R(yt,{key:1,ID:J.value,SUBJECT:W.value,DESCRIPTION:q.value,onToggleActive:Q},null,8,["ID","SUBJECT","DESCRIPTION"])):K("",!0)]),main:m(()=>[e("div",ft,[e("div",ht,[g(E,{type:"button",onClick:st,class:"m-1 ml-2 bg-navy p-10"},{default:m(()=>[g(_t,{class:"h-5"})]),_:1}),g(E,{type:"button",onClick:lt,class:"bg-navy"},{default:m(()=>t[4]||(t[4]=[A(" PICKLIST FORM ")])),_:1}),g(E,{type:"button",onClick:dt,class:"m-6 bg-navy"},{default:m(()=>t[5]||(t[5]=[A(" PRINT PL ")])),_:1}),g(E,{type:"button",onClick:at,class:"bg-navy"},{default:m(()=>t[6]||(t[6]=[A(" PRINT DR ")])),_:1}),g(E,{type:"button",onClick:ot,class:"ml-2 bg-navy"},{default:m(()=>t[7]||(t[7]=[A(" CALCULATE ")])),_:1}),S(e("select",{"onUpdate:modelValue":t[0]||(t[0]=o=>r.selectedStore=o),onChange:t[1]||(t[1]=(...o)=>r.loadStoreData&&r.loadStoreData(...o)),class:"ml-2 p-2 border rounded"},[t[8]||(t[8]=e("option",{value:""},"Select a store",-1)),(n(!0),b(h,null,f(r.storeList,o=>(n(),b("option",{key:o,value:o},a(o),9,Tt))),128))],544),[[bt,r.selectedStore]]),e("form",{onSubmit:pt(H,["prevent"]),class:"px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto"},[e("input",{type:"hidden",name:"_token",value:r.$page.props.csrf_token},null,8,Et),e("div",Ct,[e("div",It,[t[9]||(t[9]=e("div",{class:"flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none"},[e("svg",{class:"w-5 h-5 text-gray-500 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z","clip-rule":"evenodd"})])],-1)),S(e("input",{id:"StartDate",type:"date","onUpdate:modelValue":t[2]||(t[2]=o=>T(v).StartDate=o),onInput:t[3]||(t[3]=(...o)=>N.value&&N.value(...o)),placeholder:N.value,class:"bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",required:""},null,40,wt),[[z,T(v).StartDate]]),g(s,{message:T(v).errors.StartDate,class:"mt-2"},null,8,["message"])])])],32),g(vt,{type:"submit",onClick:H,disabled:T(v).processing,class:ut({"opacity-25":T(v).processing})},{default:m(()=>[g(mt,{class:"h-8"})]),_:1},8,["disabled","class"])])]),e("div",At,[t[33]||(t[33]=e("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab bg-base-200 border-base-300","aria-label":"PICK LIST",checked:""},null,-1)),e("div",Dt,[e("div",Nt,[e("div",Ot,[j.value?(n(),b("div",Rt,t[10]||(t[10]=[e("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):C.value?(n(),b("div",St,[e("p",Lt,a(C.value),1)])):!_.value||Object.keys(_.value).length===0?(n(),b("div",kt,t[11]||(t[11]=[e("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[e("p",{class:"text-gray-600 text-base sm:text-lg"},"No Pick List Available")],-1)]))):(n(!0),b(h,{key:3},f(_.value,(o,d)=>(n(),b("div",{key:d,class:"w-full mb-8"},[e("div",{class:"max-w-xl mx-auto bg-white shadow-lg",ref_for:!0,ref_key:"printableContent",ref:rt},[t[19]||(t[19]=e("div",{class:"bg-blue-800 text-white text-center py-2 font-bold"}," ELJIN CORPORATION ",-1)),e("div",Ut," PACKING LIST - "+a(d),1),e("div",Pt," DELIVERY DATE: "+a(u()),1),e("div",$t,[e("div",Mt,[t[12]||(t[12]=e("div",{class:"w-1/2 p-2 border-r border-gray-400"},"PRODUCT",-1)),e("div",Gt,a(d),1),t[13]||(t[13]=e("div",{class:"w-1/4 p-2 text-center"},"ACTUAL",-1))]),e("div",Vt,[(n(!0),b(h,null,f(o,l=>(n(),b("div",{key:l.ITEMID,class:"flex"},[e("div",jt,a(l.ITEMNAME),1),e("div",Ht,a(i(l.COUNTED)),1),e("div",Ft,[S(e("input",{"onUpdate:modelValue":c=>l.ACTUAL=c,type:"number",class:"w-full text-center border border-gray-300 rounded",onInput:c=>et(d,l.ITEMNAME,l.ITEMID,c.target.value),disabled:!l.JOURNALID||!l.ITEMID,title:!l.JOURNALID||!l.ITEMID?"Cannot update: Missing required data":""},null,40,Yt),[[z,l.ACTUAL]])])]))),128)),e("div",Bt,[t[14]||(t[14]=e("div",{class:"w-1/2 p-2 border-r border-gray-300"},"TOTAL",-1)),e("div",Kt,a(i(Z(o))),1),e("div",zt,a(i(tt(o))),1)])])]),e("div",Jt,[e("table",Wt,[e("tr",null,[t[15]||(t[15]=e("td",{class:"border-b border-r border-gray-300 p-2 text-red-600 font-semibold"},"DISPATCHER:",-1)),t[16]||(t[16]=e("td",{class:"border-b border-gray-300 p-2"},[e("div",null,"SIGN OVER PRINTED NAME"),e("div",{class:"border-b border-gray-300 mt-4"})],-1)),e("td",qt,a(u()),1)]),e("tr",null,[t[17]||(t[17]=e("td",{class:"border-r border-gray-300 p-2 font-semibold"},"LOGISTICS:",-1)),t[18]||(t[18]=e("td",{class:"p-2"},[e("div",null,"SIGN OVER PRINTED NAME"),e("div",{class:"border-b border-gray-300 mt-4"})],-1)),e("td",Qt,a(u()),1)])])]),t[20]||(t[20]=e("br",null,null,-1))],512)]))),128))])])]),t[34]||(t[34]=e("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab","aria-label":"DR"},null,-1)),e("div",Xt,[j.value?(n(),b("div",Zt,t[21]||(t[21]=[e("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):C.value?(n(),b("div",te,[e("p",ee,a(C.value),1)])):!D.value||Object.keys(D.value).length===0?(n(),b("div",re,t[22]||(t[22]=[e("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[e("p",{class:"text-gray-600 text-base sm:text-lg"},"No DR List Available")],-1)]))):(n(!0),b(h,{key:3},f(D.value,(o,d)=>(n(),b("div",{key:d,class:"max-w-3xl mx-auto bg-gray-100 p-8 mb-8"},[t[31]||(t[31]=e("div",{class:"text-center mb-4"},[e("h1",{class:"font-bold"},"ELIIN CORPORATION"),e("p",null,"MALIWALO"),e("p",null,"TARLAC CITY"),e("h1",{class:"font-bold"},"DELIVERY GOODS RECEIPT: BW PRODUCTS")],-1)),e("div",oe,[e("div",null,[e("p",null,"DR #: "+a(o[0].JOURNALID),1),e("p",null,"DELIVERY DATE: "+a(k(o[0].POSTEDDATETIME)),1)]),t[24]||(t[24]=e("div",null,[e("p",null,"RECIEVED FROM:"),e("p",null,"HEADOFFICE")],-1)),e("div",null,[t[23]||(t[23]=e("p",null,"DELIVERED TO:",-1)),e("p",null,a(d),1)])]),e("table",se,[t[30]||(t[30]=e("thead",null,[e("tr",{class:"bg-gray-200"},[e("th",{class:"border border-gray-300 p-2"},"PRODUCT DESCRIPTION"),e("th",{class:"border border-gray-300 p-2"},"TARGET"),e("th",{class:"border border-gray-300 p-2"},"ALLOC"),e("th",{class:"border border-gray-300 p-2"},"TOTAL"),e("th",{class:"border border-gray-300 p-2"},"RECEIVE QUANTITY"),e("th",{class:"border border-gray-300 p-2"},"TRANSFER COST"),e("th",{class:"border border-gray-300 p-2"},"TOTAL AMOUNT")])],-1)),e("tbody",null,[(n(!0),b(h,null,f(o,l=>(n(),b("tr",{key:l.ITEMID},[e("td",le,a(l.ITEMNAME),1),e("td",de,a(i(l.CHECKINGCOUNT)),1),t[25]||(t[25]=e("td",{class:"border border-gray-300 p-2 text-center"},"0",-1)),e("td",ae,a(i(Number(l.CHECKINGCOUNT))),1),t[26]||(t[26]=e("td",{class:"border border-gray-300 p-2 text-center"},null,-1)),e("td",ne,a(y(l.COST)),1),e("td",ie,a(y(Number(l.COST)*Number(l.CHECKINGCOUNT))),1)]))),128)),e("tr",ce,[t[27]||(t[27]=e("td",{class:"border border-gray-300 p-2"},"TOTAL",-1)),e("td",be,a(i(P(o))),1),t[28]||(t[28]=e("td",{class:"border border-gray-300 p-2 text-center"},"0",-1)),e("td",pe,a(i(U(o))),1),t[29]||(t[29]=e("td",{class:"border border-gray-300 p-2 text-center"},null,-1)),e("td",ue,a(y($(o))),1),e("td",ge,a(y(M(o))),1)])])]),t[32]||(t[32]=e("div",{class:"mt-8 flex justify-between"},[e("div",null,[e("p",null,"ENDORSED BY: DISPATCHING"),e("p",null,"_____________________________"),e("p",null,"NAME & SIGNATURE / DATE")]),e("div",null,[e("p",null,"RECEIVED BY STORE"),e("p",null,"_____________________________"),e("p",null,"NAME & SIGNATURE / DATE")])],-1))]))),128))])])]),_:1})}}};export{Se as default};
