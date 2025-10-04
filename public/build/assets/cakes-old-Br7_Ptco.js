import{D as H,Q as nt,d as p,T as it,s as Y,p as ct,c as R,w as y,o as n,f as B,a as e,b as u,g as w,i as bt,j as K,v as z,u as m,n as pt,e as b,t as d,h as I,F as A}from"./app-Do8mhcc_.js";import{_ as ut,a as gt}from"./Update-CGm56EGF.js";import{_ as x}from"./Modal.vue_vue_type_style_index_0_scoped_fd08cd24_lang-Cw8u42Sx.js";import{_ as _t}from"./TransparentButton-EuWNYQiU.js";import{S as yt}from"./SearchColored-BdPcGYYt.js";import{B as ht}from"./Back-DH6hWANX.js";import{_ as vt}from"./AdminPanel-B-hw9tyY.js";import"./Modal-BufSFMTr.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./InputError-Bl4xi_3e.js";import"./FormComponent-DbpXKqv4.js";import"./RetailGroup-D_6SfRWw.js";import"./Logout-MAsyIaFu.js";/* empty css                                                             */import"./RetailItems-SitUjnLk.js";import"./Attendance-Bi3B0c_5.js";const ft={class:"absolute adjust"},mt={class:"flex justify-start items-center"},xt=["value"],Tt={"date-rangepicker":"",class:"flex items-center"},Et={class:"relative ml-5"},Ct=["placeholder"],wt={role:"tablist",class:"tabs tabs-lifted mt-10 p-5"},It={role:"tabpanel",class:"tab-content bg-base-200 border-base-300 p-6 h-[70vh] overflow-y-auto"},At={class:"container mx-auto px-4"},Dt={class:"flex flex-wrap -mx-4"},Ot={key:0,class:"col-span-full text-center mt-8"},Nt={key:1,class:"col-span-full text-center mt-8"},Rt={class:"text-red-600 text-lg"},St={key:2,class:"col-span-full text-center mt-8"},Lt={class:"bg-blue-600 text-white text-center py-1 font-semibold"},kt={class:"bg-blue-400 text-white text-center py-1"},Ut={class:"w-full px-4 mb-8"},$t={class:"flex bg-gray-200 font-semibold"},Pt={class:"w-1/4 p-2 text-center border-r border-gray-400"},Mt={class:"divide-y divide-gray-300"},Vt={class:"w-1/2 p-2 border-r border-gray-300"},Gt={class:"w-1/4 p-2 text-center border-r border-gray-300"},jt={class:"w-1/4 p-2 text-center"},Ft=["onUpdate:modelValue","onInput","disabled","title"],Ht={class:"flex bg-red-200"},Yt={class:"w-1/4 p-2 text-center border-r border-gray-300"},Bt={class:"w-1/4 p-2 text-center"},Kt={role:"tabpanel",class:"tab-content bg-base-100 border-base-200 p-6 h-[85vh] overflow-y-auto"},zt={key:0,class:"col-span-full text-center mt-8"},Jt={key:1,class:"col-span-full text-center mt-8"},Wt={class:"text-red-600 text-lg"},qt={key:2,class:"col-span-full text-center mt-8"},Qt={class:"flex justify-between mb-4"},Xt={class:"w-full border-collapse border border-gray-300"},Zt={class:"border border-gray-300 p-2"},te={class:"border border-gray-300 p-2 text-center"},ee={class:"border border-gray-300 p-2 text-center"},re={class:"border border-gray-300 p-2 text-right"},oe={class:"border border-gray-300 p-2 text-right"},se={class:"bg-gray-200 font-bold"},ae={class:"border border-gray-300 p-2 text-center"},le={class:"border border-gray-300 p-2 text-center"},de={class:"border border-gray-300 p-2 text-right"},ne={class:"border border-gray-300 p-2 text-right"},Ie={__name:"cakes-old",setup(ie){H.defaults.headers.common["X-CSRF-TOKEN"]=document.querySelector('meta[name="csrf-token"]').getAttribute("content");const S=nt(),h=p(Object.entries(S.props.groupedPicklist).reduce((r,[t,s])=>(r[t]=s.map(o=>({...o,actual:o.ACTUAL})),r),{})),D=p(Object.entries(S.props.groupedPicklist).reduce((r,[t,s])=>(r[t]=s.map(o=>({...o,actual:o.ACTUAL})),r),{})),L=r=>new Date(r).toLocaleDateString(),g=r=>new Intl.NumberFormat("en-PH",{style:"currency",currency:"PHP"}).format(r),k=r=>r.reduce((t,s)=>t+Number(s.CHECKINGCOUNT||0),0),U=r=>r.reduce((t,s)=>t+Number(s.CHECKINGCOUNT||0),0),$=r=>r.reduce((t,s)=>t+Number(s.COST||0),0),P=r=>r.reduce((t,s)=>t+Number(s.COST||0)*Number(s.CHECKINGCOUNT||0),0),J=p(""),W=p(""),q=p(""),M=p(!1),V=p(!1),G=p(!1),T=p(null),_=it({StartDate:"2024-07-22",StoreName:"Urdaneta2"}),j=()=>{_.get(route("picklist.getrange"),{preserveScroll:!0})};p(null);const O=Y(()=>{const r=_.StartDate;if(r){const t=new Date(r),s=t.getFullYear(),o=String(t.getMonth()+1).padStart(2,"0"),l=String(t.getDate()).padStart(2,"0");return`${s}-${o}-${l}`}return""});p(null),Y(()=>{const r=_.EndDate;if(r){const t=new Date(r),s=t.getFullYear(),o=String(t.getMonth()+1).padStart(2,"0"),l=String(t.getDate()).padStart(2,"0");return`${s}-${o}-${l}`}return""});const Q=()=>{M.value=!1},X=()=>{V.value=!1},Z=r=>r.reduce((t,s)=>t+parseFloat(s.COUNTED||0),0),E=(r,t)=>r.reduce((s,o)=>s+(o[t]||0),0),tt=r=>r.reduce((t,s)=>t+parseFloat(s.actual||0),0),v=()=>{const r=new Date;return`${r.getMonth()+1}/${r.getDate()}/${r.getFullYear().toString().substr(-2)}`},i=r=>{const t=parseFloat(r);return Number.isInteger(t)?t.toString():Math.round(t).toString()},et=async(r,t,s,o)=>{try{const a=h.value[r].find(f=>f.ITEMID===s);if(!a){console.error("Item not found");return}if(!a.JOURNALID){console.error("JOURNALID is missing for this item");return}const c=await H.post("/api/update-actual",{journal_id:a.JOURNALID,store_name:r,item_name:t,item_id:s,actual:o});c.data.success?a.ACTUAL=o:console.error("Failed to update ACTUAL value",c.data)}catch(l){l.response&&l.response.data?(console.error("Server validation errors:",l.response.data.errors),Object.entries(l.response.data.errors).forEach(([a,c])=>{console.error(`${a}: ${c.join(", ")}`)})):console.error("Error updating ACTUAL value:",l.message)}},rt=p(null),ot=()=>{window.location.href="/cakepicklist"},st=()=>{window.location.href="/mgcount"},at=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),t=Object.entries(h.value);let s="";for(let o=0;o<t.length;o+=2){const l=t.slice(o,o+2).map(([a,c])=>{const f=c.map(F=>`
        <tr>
          <td class="border p-1">${F.ITEMNAME}</td>
          <td class="border p-1 text-center">${i(F.COUNTED)}</td>
          <td class="border p-1 text-center">0</td>
        </tr>
      `).join(""),N=E(c,"COUNTED"),C=E(c,"ACTUAL");return`
        <div class="store-section">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST - ${a}</div>
          <div class="bg-blue-400">DELIVERY DATE: ${v()}</div>
          <table>
            <thead>
              <tr>
                <th class="border p-1">PRODUCT</th>
                <th class="border p-1">${a}</th>
                <th class="border p-1">ACTUAL</th>
              </tr>
            </thead>
            <tbody>
              ${f}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${i(N)}</td>
                <td class="border p-1 text-center font-bold">${i(C)}</td>
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
                <td class="text-right" style="width: 20%;">${v()}</td>
              </tr>
              <tr>
                <td class="font-semibold" style="width: 20%;">LOGISTICS:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">${v()}</td>
              </tr>
            </tbody>
          </table>
        </div>
      `}).join("");s+=`
      <div class="page-container">
        ${l}
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
            font-size: 16px;
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
            font-size: 16px; 
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
  `),r.document.close(),r.focus(),r.print(),r.close()},lt=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0");let t="";for(const[s,o]of Object.entries(h.value))t+=`
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
            <p>DELIVERY DATE: ${L(o[0].POSTEDDATETIME)}</p>
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
            ${o.map(l=>`
              <tr>
                <td class="border border-gray-300 p-2">${l.ITEMNAME}</td>
                <td class="border border-gray-300 p-2 text-center">${i(l.CHECKINGCOUNT)}</td>
                <td class="border border-gray-300 p-2 text-center">0</td>
                <td class="border border-gray-300 p-2 text-center">${i(Number(l.CHECKINGCOUNT))}</td>
                <td class="border border-gray-300 p-2 text-center"></td>
                <td class="border border-gray-300 p-2 text-right">${g(l.COST)}</td>
                <td class="border border-gray-300 p-2 text-right">${g(Number(l.COST)*Number(l.CHECKINGCOUNT))}</td>
              </tr>
            `).join("")}
            <tr class="bg-gray-200 font-bold">
              <td class="border border-gray-300 p-2">TOTAL</td>
              <td class="border border-gray-300 p-2 text-center">${i(U(o))}</td>
              <td class="border border-gray-300 p-2 text-center">0</td>
              <td class="border border-gray-300 p-2 text-center">${i(k(o))}</td>
              <td class="border border-gray-300 p-2 text-center"></td>
              <td class="border border-gray-300 p-2 text-right">${g($(o))}</td>
              <td class="border border-gray-300 p-2 text-right">${g(P(o))}</td>
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
            font-size: 16px;
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
            font-size: 16px; 
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
  `),r.document.close(),r.focus(),r.print(),r.close()},dt=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),t=Object.entries(h.value);let s="";for(let o=0;o<t.length;o+=2){const l=t.slice(o,o+2).map(([a,c])=>{const f=c.map(C=>`
        <tr>
          <td class="border p-1">${C.ITEMNAME}</td>
          <td class="border p-1 text-center">${i(C.COUNTED)}</td>
          <td class="border p-1 text-center"></td>
        </tr>
      `).join(""),N=E(c,"COUNTED");return E(c,"ACTUAL"),`
        <div class="store-section">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST - ${a}</div>
          <div class="bg-blue-400">DELIVERY DATE: ${v()}</div>
          <table>
            <thead>
              <tr>
                <th class="border p-1">PRODUCT</th>
                <th class="border p-1">${a}</th>
                <th class="border p-1">ACTUAL</th>
              </tr>
            </thead>
            <tbody>
              ${f}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${i(N)}</td>
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
                <td class="text-right" style="width: 20%;">${v()}</td>
              </tr>
              <tr>
                <td class="font-semibold" style="width: 20%;">LOGISTICS:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">${v()}</td>
              </tr>
            </tbody>
          </table>
        </div>
      `}).join("");s+=`
      <div class="page-container">
        ${l}
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
            font-size: 16px;
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
            font-size: 16px; 
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
  `),r.document.close(),r.focus(),r.print(),r.close()};return(r,t)=>{const s=ct("InputError");return n(),R(vt,{"active-tab":"PICKLIST"},{modals:y(()=>[V.value?(n(),R(ut,{key:0,onToggleActive:X})):B("",!0),M.value?(n(),R(gt,{key:1,ID:J.value,SUBJECT:W.value,DESCRIPTION:q.value,onToggleActive:Q},null,8,["ID","SUBJECT","DESCRIPTION"])):B("",!0)]),main:y(()=>[e("div",ft,[e("div",mt,[u(x,{type:"button",onClick:st,class:"m-1 ml-2 bg-navy p-10"},{default:y(()=>[u(ht,{class:"h-5"})]),_:1}),u(x,{type:"button",onClick:dt,class:"bg-navy"},{default:y(()=>t[2]||(t[2]=[w(" PICKLIST FORM ")])),_:1}),u(x,{type:"button",onClick:at,class:"m-6 bg-navy"},{default:y(()=>t[3]||(t[3]=[w(" PRINT PL ")])),_:1}),u(x,{type:"button",onClick:lt,class:"bg-navy"},{default:y(()=>t[4]||(t[4]=[w(" PRINT DR ")])),_:1}),u(x,{type:"button",onClick:ot,class:"ml-2 bg-navy"},{default:y(()=>t[5]||(t[5]=[w(" SAVE ")])),_:1}),e("form",{onSubmit:bt(j,["prevent"]),class:"px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto"},[e("input",{type:"hidden",name:"_token",value:r.$page.props.csrf_token},null,8,xt),e("div",Tt,[e("div",Et,[t[6]||(t[6]=e("div",{class:"flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none"},[e("svg",{class:"w-5 h-5 text-gray-500 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[e("path",{"fill-rule":"evenodd",d:"M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z","clip-rule":"evenodd"})])],-1)),K(e("input",{id:"StartDate",type:"date","onUpdate:modelValue":t[0]||(t[0]=o=>m(_).StartDate=o),onInput:t[1]||(t[1]=(...o)=>O.value&&O.value(...o)),placeholder:O.value,class:"bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",required:""},null,40,Ct),[[z,m(_).StartDate]]),u(s,{message:m(_).errors.StartDate,class:"mt-2"},null,8,["message"])])])],32),u(_t,{type:"submit",onClick:j,disabled:m(_).processing,class:pt({"opacity-25":m(_).processing})},{default:y(()=>[u(yt,{class:"h-8"})]),_:1},8,["disabled","class"])])]),e("div",wt,[t[26]||(t[26]=e("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab bg-base-200 border-base-300","aria-label":"PICK LIST",checked:""},null,-1)),e("div",It,[e("div",At,[e("div",Dt,[G.value?(n(),b("div",Ot,t[7]||(t[7]=[e("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):T.value?(n(),b("div",Nt,[e("p",Rt,d(T.value),1)])):!h.value||Object.keys(h.value).length===0?(n(),b("div",St,t[8]||(t[8]=[e("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[e("p",{class:"text-gray-600 text-base sm:text-lg"},"No Pick List Available")],-1)]))):(n(!0),b(A,{key:3},I(h.value,(o,l)=>(n(),b("div",{key:l,class:"w-full mb-8"},[e("div",{class:"max-w-xl mx-auto bg-white shadow-lg",ref_for:!0,ref_key:"printableContent",ref:rt},[t[12]||(t[12]=e("div",{class:"bg-blue-800 text-white text-center py-2 font-bold"}," ELJIN CORPORATION ",-1)),e("div",Lt," PACKING LIST - "+d(l),1),e("div",kt," DELIVERY DATE: "+d(v()),1),e("div",Ut,[e("div",$t,[t[9]||(t[9]=e("div",{class:"w-1/2 p-2 border-r border-gray-400"},"PRODUCT",-1)),e("div",Pt,d(l),1),t[10]||(t[10]=e("div",{class:"w-1/4 p-2 text-center"},"ACTUAL",-1))]),e("div",Mt,[(n(!0),b(A,null,I(o,a=>(n(),b("div",{key:a.ITEMID,class:"flex"},[e("div",Vt,d(a.ITEMNAME),1),e("div",Gt,d(i(a.COUNTED)),1),e("div",jt,[K(e("input",{"onUpdate:modelValue":c=>a.ACTUAL=c,type:"number",class:"w-full text-center border border-gray-300 rounded",onInput:c=>et(l,a.ITEMNAME,a.ITEMID,c.target.value),disabled:!a.JOURNALID||!a.ITEMID,title:!a.JOURNALID||!a.ITEMID?"Cannot update: Missing required data":""},null,40,Ft),[[z,a.ACTUAL]])])]))),128)),e("div",Ht,[t[11]||(t[11]=e("div",{class:"w-1/2 p-2 border-r border-gray-300"},"TOTAL",-1)),e("div",Yt,d(i(Z(o))),1),e("div",Bt,d(i(tt(o))),1)])])]),t[13]||(t[13]=e("br",null,null,-1))],512)]))),128))])])]),t[27]||(t[27]=e("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab","aria-label":"DR"},null,-1)),e("div",Kt,[G.value?(n(),b("div",zt,t[14]||(t[14]=[e("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):T.value?(n(),b("div",Jt,[e("p",Wt,d(T.value),1)])):!D.value||Object.keys(D.value).length===0?(n(),b("div",qt,t[15]||(t[15]=[e("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[e("p",{class:"text-gray-600 text-base sm:text-lg"},"No DR List Available")],-1)]))):(n(!0),b(A,{key:3},I(D.value,(o,l)=>(n(),b("div",{key:l,class:"max-w-3xl mx-auto bg-gray-100 p-8 mb-8"},[t[24]||(t[24]=e("div",{class:"text-center mb-4"},[e("h1",{class:"font-bold"},"ELIIN CORPORATION"),e("p",null,"MALIWALO"),e("p",null,"TARLAC CITY"),e("h1",{class:"font-bold"},"DELIVERY GOODS RECEIPT: BW PRODUCTS")],-1)),e("div",Qt,[e("div",null,[e("p",null,"DR #: "+d(o[0].JOURNALID),1),e("p",null,"DELIVERY DATE: "+d(L(o[0].POSTEDDATETIME)),1)]),t[17]||(t[17]=e("div",null,[e("p",null,"RECIEVED FROM:"),e("p",null,"HEADOFFICE")],-1)),e("div",null,[t[16]||(t[16]=e("p",null,"DELIVERED TO:",-1)),e("p",null,d(l),1)])]),e("table",Xt,[t[23]||(t[23]=e("thead",null,[e("tr",{class:"bg-gray-200"},[e("th",{class:"border border-gray-300 p-2"},"PRODUCT DESCRIPTION"),e("th",{class:"border border-gray-300 p-2"},"TARGET"),e("th",{class:"border border-gray-300 p-2"},"ALLOC"),e("th",{class:"border border-gray-300 p-2"},"TOTAL"),e("th",{class:"border border-gray-300 p-2"},"RECEIVE QUANTITY"),e("th",{class:"border border-gray-300 p-2"},"TRANSFER COST"),e("th",{class:"border border-gray-300 p-2"},"TOTAL AMOUNT")])],-1)),e("tbody",null,[(n(!0),b(A,null,I(o,a=>(n(),b("tr",{key:a.ITEMID},[e("td",Zt,d(a.ITEMNAME),1),e("td",te,d(i(a.CHECKINGCOUNT)),1),t[18]||(t[18]=e("td",{class:"border border-gray-300 p-2 text-center"},"0",-1)),e("td",ee,d(i(Number(a.CHECKINGCOUNT))),1),t[19]||(t[19]=e("td",{class:"border border-gray-300 p-2 text-center"},null,-1)),e("td",re,d(g(a.COST)),1),e("td",oe,d(g(Number(a.COST)*Number(a.CHECKINGCOUNT))),1)]))),128)),e("tr",se,[t[20]||(t[20]=e("td",{class:"border border-gray-300 p-2"},"TOTAL",-1)),e("td",ae,d(i(U(o))),1),t[21]||(t[21]=e("td",{class:"border border-gray-300 p-2 text-center"},"0",-1)),e("td",le,d(i(k(o))),1),t[22]||(t[22]=e("td",{class:"border border-gray-300 p-2 text-center"},null,-1)),e("td",de,d(g($(o))),1),e("td",ne,d(g(P(o))),1)])])]),t[25]||(t[25]=e("div",{class:"mt-8 flex justify-between"},[e("div",null,[e("p",null,"ENDORSED BY: DISPATCHING"),e("p",null,"_____________________________"),e("p",null,"NAME & SIGNATURE / DATE")]),e("div",null,[e("p",null,"RECEIVED BY STORE"),e("p",null,"_____________________________"),e("p",null,"NAME & SIGNATURE / DATE")])],-1))]))),128))])])]),_:1})}}};export{Ie as default};
