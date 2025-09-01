import{C as G,Q as lt,d as p,T as dt,q as V,p as at,c as D,w as T,o as n,f as j,a as t,b as u,g as R,i as nt,j as H,s as it,u as N,e as b,h,t as a,F as f,n as ct,v as bt}from"./app-BNPGhoL2.js";import{_ as pt,a as ut}from"./Update-7MSkeTrO.js";import{_ as x}from"./Modal.vue_vue_type_style_index_0_scoped_fd08cd24_lang-B6Xc1nH0.js";import{_ as gt}from"./TransparentButton-DNYsCaht.js";import{S as Tt}from"./SearchColored-CHmHwtyG.js";import{B as yt}from"./Back-BFtAAhoj.js";import{F as mt}from"./File-fSn_QkHF.js";import{_ as _t}from"./AdminPanel-C3by1daL.js";import"./Modal-k3aPgSoE.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./InputError-AsoMIJ7j.js";import"./FormComponent-k5Mz26hM.js";import"./RetailGroup-afsdHTlZ.js";import"./Logout-B5n2G0rZ.js";/* empty css                                                             */import"./RetailItems-BzVTUMDz.js";import"./Attendance-D3nGk9-s.js";const ht={class:"absolute adjust"},ft={class:"flex justify-start items-center"},xt=["value"],Et={class:"mr-2"},vt={role:"tablist",class:"tabs tabs-lifted mt-10 p-5"},Ot={role:"tabpanel",class:"tab-content bg-base-200 border-base-300 p-6 h-[70vh] overflow-y-auto"},Nt={class:"container mx-auto px-4"},Ct={class:"flex flex-wrap -mx-4"},It={key:0,class:"col-span-full text-center mt-8"},At={key:1,class:"col-span-full text-center mt-8"},wt={class:"text-red-600 text-lg"},Dt={key:2,class:"col-span-full text-center mt-8"},Rt={class:"bg-blue-600 text-white text-center py-1 font-semibold"},St={class:"w-full px-4 mb-8"},Lt={class:"flex bg-gray-200 font-semibold"},kt={class:"w-1/4 p-2 text-center border-r border-gray-400"},Pt={class:"divide-y divide-gray-300"},Ut={class:"w-1/2 p-2 border-r border-gray-300"},Mt={class:"w-1/4 p-2 text-center border-r border-gray-300"},$t={class:"w-1/4 p-2 text-center"},Gt=["onUpdate:modelValue","onInput","disabled","title"],Vt={class:"flex bg-red-200"},jt={class:"w-1/4 p-2 text-center border-r border-gray-300"},Ht={class:"w-1/4 p-2 text-center"},Ft={role:"tabpanel",class:"tab-content bg-base-100 border-base-200 p-6 h-[85vh] overflow-y-auto"},Yt={key:0,class:"col-span-full text-center mt-8"},Bt={key:1,class:"col-span-full text-center mt-8"},Kt={class:"text-red-600 text-lg"},zt={key:2,class:"col-span-full text-center mt-8"},Jt={class:"flex justify-between mb-4"},Wt={class:"w-full border-collapse border border-gray-300"},qt={class:"border border-gray-300 p-2"},Qt={class:"border border-gray-300 p-2 text-center"},Xt={class:"border border-gray-300 p-2 text-center"},Zt={class:"border border-gray-300 p-2 text-right"},te={class:"border border-gray-300 p-2 text-right"},ee={class:"bg-gray-200 font-bold"},re={class:"border border-gray-300 p-2 text-center"},se={class:"border border-gray-300 p-2 text-center"},oe={class:"border border-gray-300 p-2 text-right"},le={class:"border border-gray-300 p-2 text-right"},Oe={__name:"Index",setup(de){G.defaults.headers.common["X-CSRF-TOKEN"]=document.querySelector('meta[name="csrf-token"]').getAttribute("content");const C=lt(),F=p(C.props.rbostoretables),y=p(Object.entries(C.props.groupedPicklist).reduce((r,[e,o])=>(r[e]=o.map(s=>({...s,actual:s.ACTUAL})),r),{})),I=p(Object.entries(C.props.groupedPicklist).reduce((r,[e,o])=>(r[e]=o.map(s=>({...s,actual:s.ACTUAL})),r),{})),g=r=>new Intl.NumberFormat("en-PH",{style:"currency",currency:"PHP"}).format(r),S=r=>r.reduce((e,o)=>e+Number(o.CHECKINGCOUNT||0),0),L=r=>r.reduce((e,o)=>e+Number(o.CHECKINGCOUNT||0),0),k=r=>r.reduce((e,o)=>e+Number(o.COST||0),0),P=r=>r.reduce((e,o)=>e+Number(o.COST||0)*Number(o.CHECKINGCOUNT||0),0),Y=p(""),B=p(""),K=p(""),U=p(!1),M=p(!1),$=p(!1),E=p(null),m=dt({STORE:""}),z=()=>{m.get(route("picklist.getstore"),{preserveScroll:!0})};p(null),V(()=>{const r=m.StartDate;if(r){const e=new Date(r),o=e.getFullYear(),s=String(e.getMonth()+1).padStart(2,"0"),d=String(e.getDate()).padStart(2,"0");return`${o}-${s}-${d}`}return""}),p(null),V(()=>{const r=m.EndDate;if(r){const e=new Date(r),o=e.getFullYear(),s=String(e.getMonth()+1).padStart(2,"0"),d=String(e.getDate()).padStart(2,"0");return`${o}-${s}-${d}`}return""});const J=()=>{U.value=!1},W=()=>{M.value=!1},q=r=>r.reduce((e,o)=>e+parseFloat(o.COUNTED||0),0),v=(r,e)=>r.reduce((o,s)=>o+(s[e]||0),0),Q=r=>r.reduce((e,o)=>e+parseFloat(o.actual||0),0),i=r=>{const e=parseFloat(r);return Number.isInteger(e)?e.toString():Math.round(e).toString()},X=async(r,e,o,s)=>{try{const l=y.value[r].find(_=>_.ITEMID===o);if(!l){console.error("Item not found");return}if(!l.JOURNALID){console.error("JOURNALID is missing for this item");return}const c=await G.post("/api/update-actual",{journal_id:l.JOURNALID,store_name:r,item_name:e,item_id:o,actual:s});c.data.success?l.ACTUAL=s:console.error("Failed to update ACTUAL value",c.data)}catch(d){d.response&&d.response.data?(console.error("Server validation errors:",d.response.data.errors),Object.entries(d.response.data.errors).forEach(([l,c])=>{console.error(`${l}: ${c.join(", ")}`)})):console.error("Error updating ACTUAL value:",d.message)}},Z=p(null),tt=()=>{window.location.href="/picklist"},et=()=>{window.location.href="/mgcount"},rt=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),e=Object.entries(y.value);let o="";for(let s=0;s<e.length;s+=2){const d=e.slice(s,s+2).map(([l,c])=>{const _=c.map(O=>`
        <tr>
          <td class="border p-1">${O.ITEMNAME}</td>
          <td class="border p-1 text-center">${i(O.COUNTED)}</td>
          <td class="border p-1 text-center"></td>
        </tr>
      `).join(""),A=v(c,"COUNTED");return v(c,"ACTUAL"),`
        <div class="store-section">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST - ${l}</div>
          <div class="bg-blue-400">DELIVERY DATE: NOT POSTED</div>
          <table>
            <thead>
              <tr>
                <th class="border p-1">PRODUCT</th>
                <th class="border p-1">${l}</th>
                <th class="border p-1">ACTUAL</th>
              </tr>
            </thead>
            <tbody>
              ${_}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${i(A)}</td>
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
                <td class="text-right" style="width: 20%;">NOT POSTED</td>
              </tr>
              <tr>
                <td class="font-semibold" style="width: 20%;">LOGISTICS:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">NOT POSTED</td>
              </tr>
            </tbody>
          </table>
        </div>
      `}).join("");o+=`
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
        ${o}
      </body>
    </html>
  `),r.document.close(),r.focus(),r.print(),r.close()},st=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),e=Object.entries(y.value);let o="";for(let s=0;s<e.length;s+=2){const d=e.slice(s,s+2).map(([l,c])=>{const _=c.map(w=>`
        <tr>
          <td class="border p-1">${w.ITEMNAME}</td>
          <td class="border p-1 text-center">${i(w.COUNTED)}</td>
          <td class="border p-1 text-center">${i(w.CHECKINGCOUNT)}</td>
        </tr>
      `).join(""),A=v(c,"COUNTED"),O=v(c,"ACTUAL");return`
        <div class="store-section">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST - ${l}</div>
          <div class="bg-blue-400">DELIVERY DATE: NOT POSTED</div>
          <table>
            <thead>
              <tr>
                <th class="border p-1">PRODUCT</th>
                <th class="border p-1">${l}</th>
                <th class="border p-1">ACTUAL</th>
              </tr>
            </thead>
            <tbody>
              ${_}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${i(A)}</td>
                <td class="border p-1 text-center font-bold">${i(O)}</td>
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
                <td class="text-right" style="width: 20%;">NOT POSTED</td>
              </tr>
              <tr>
                <td class="font-semibold" style="width: 20%;">LOGISTICS:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">NOT POSTED</td>
              </tr>
            </tbody>
          </table>
        </div>
      `}).join("");o+=`
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
        ${o}
      </body>
    </html>
  `),r.document.close(),r.focus(),r.print(),r.close()},ot=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0");let e="";for(const[o,s]of Object.entries(y.value))e+=`
      <div class="receipt-page">
        <div class="text-center mb-4">
          <h3 class="font-bold">ELIIN CORPORATION</h3>
          <p>MALIWALO</p>
          <p>TARLAC CITY</p>
          <h3 class="font-bold">DELIVERY GOODS RECEIPT: BW PRODUCTS</h3>
        </div>
        
        <div class="flex justify-between mb-4">
          <div>
            <p>DR #: ${s[0].JOURNALID}</p> 
            <p>DELIVERY DATE: NOT POSTED</p>
          </div>
          <div>
            <p>RECIEVED FROM:</p>
            <p>HEADOFFICE</p>
          </div>
          <div>
            <p>DELIVERED TO:</p>
            <p>${o}</p>
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
            ${s.map(d=>`
              <tr>
                <td class="border border-gray-300 p-2">${d.ITEMNAME}</td>
                <td class="border border-gray-300 p-2 text-center">${i(d.CHECKINGCOUNT)}</td>
                <td class="border border-gray-300 p-2 text-center">0</td>
                <td class="border border-gray-300 p-2 text-center">${i(Number(d.CHECKINGCOUNT))}</td>
                <td class="border border-gray-300 p-2 text-center"></td>
                <td class="border border-gray-300 p-2 text-right">${g(d.COST)}</td>
                <td class="border border-gray-300 p-2 text-right">${g(Number(d.COST)*Number(d.CHECKINGCOUNT))}</td>
              </tr>
            `).join("")}
            <tr class="bg-gray-200 font-bold">
              <td class="border border-gray-300 p-2">TOTAL</td>
              <td class="border border-gray-300 p-2 text-center">${i(L(s))}</td>
              <td class="border border-gray-300 p-2 text-center">0</td>
              <td class="border border-gray-300 p-2 text-center">${i(S(s))}</td>
              <td class="border border-gray-300 p-2 text-center"></td>
              <td class="border border-gray-300 p-2 text-right">${g(k(s))}</td>
              <td class="border border-gray-300 p-2 text-right">${g(P(s))}</td>
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
        ${e}
      </body>
    </html>
  `),r.document.close(),r.focus(),r.print(),r.close()};return(r,e)=>{const o=at("InputLabel");return n(),D(_t,{"active-tab":"INVENTORY"},{modals:T(()=>[M.value?(n(),D(pt,{key:0,onToggleActive:W})):j("",!0),U.value?(n(),D(ut,{key:1,ID:Y.value,SUBJECT:B.value,DESCRIPTION:K.value,onToggleActive:J},null,8,["ID","SUBJECT","DESCRIPTION"])):j("",!0)]),main:T(()=>[t("div",ht,[t("div",ft,[u(x,{type:"button",onClick:et,class:"m-1 ml-2 bg-navy p-10 mt-4"},{default:T(()=>[u(yt,{class:"h-5"})]),_:1}),u(x,{type:"button",onClick:rt,class:"bg-navy tooltip tooltip-right tooltip-primary mt-4","data-tip":"Packing List Template"},{default:T(()=>[u(mt,{class:"h-5"})]),_:1}),u(x,{type:"button",onClick:st,class:"ml-1 mt-4 bg-navy"},{default:T(()=>e[1]||(e[1]=[R(" PRINT PL ")])),_:1}),u(x,{type:"button",onClick:ot,class:"ml-1 mt-4 bg-navy"},{default:T(()=>e[2]||(e[2]=[R(" PRINT DR ")])),_:1}),u(x,{type:"button",onClick:tt,class:"ml-2 bg-navy mt-4"},{default:T(()=>e[3]||(e[3]=[R(" CALCULATE ")])),_:1}),e[5]||(e[5]=t("details",{className:"dropdown"},[t("summary",{className:"btn m-1 mt-4  bg-green-900 text-white hover:bg-navy"},"Select Route"),t("ul",{className:"menu dropdown-content bg-base-100 rounded-box z-[1] w-52 p-2 shadow"},[t("li",null,[t("a",{href:"/picklist"},"ALL")]),t("li",null,[t("a",{href:"/pl-south1"},"SOUTH 1")]),t("li",null,[t("a",{href:"/pl-south2"},"SOUTH 2")]),t("li",null,[t("a",{href:"/pl-south3"},"SOUTH 3")]),t("li",null,[t("a",{href:"/pl-north1"},"NORTH 1")]),t("li",null,[t("a",{href:"/pl-north2"},"NORTH 2")]),t("li",null,[t("a",{href:"/pl-central"},"CENTRAL")]),t("li",null,[t("a",{href:"/pl-east"},"EAST")])])],-1)),t("form",{onSubmit:nt(z,["prevent"]),class:"flex items-center mt-4"},[t("input",{type:"hidden",name:"_token",value:r.$page.props.csrf_token},null,8,xt),t("div",Et,[u(o,{for:"STORE",value:"STORE",class:"sr-only"}),H(t("select",{id:"STORE","onUpdate:modelValue":e[0]||(e[0]=s=>N(m).STORE=s),class:"input input-bordered w-64"},[e[4]||(e[4]=t("option",{disabled:"",value:""},"Select Store",-1)),(n(!0),b(f,null,h(F.value,s=>(n(),b("option",{key:s.STOREID},a(s.NAME),1))),128))],512),[[it,N(m).STORE]])]),u(gt,{type:"submit",disabled:N(m).processing,class:ct({"opacity-25":N(m).processing})},{default:T(()=>[u(Tt,{class:"h-8"})]),_:1},8,["disabled","class"])],32)])]),t("div",vt,[e[28]||(e[28]=t("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab bg-base-200 border-base-300","aria-label":"PICK LIST",checked:""},null,-1)),t("div",Ot,[t("div",Nt,[t("div",Ct,[$.value?(n(),b("div",It,e[6]||(e[6]=[t("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):E.value?(n(),b("div",At,[t("p",wt,a(E.value),1)])):!y.value||Object.keys(y.value).length===0?(n(),b("div",Dt,e[7]||(e[7]=[t("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[t("p",{class:"text-gray-600 text-base sm:text-lg"},"No Pick List Available")],-1)]))):(n(!0),b(f,{key:3},h(y.value,(s,d)=>(n(),b("div",{key:d,class:"w-full mb-8"},[t("div",{class:"max-w-xl mx-auto bg-white shadow-lg",ref_for:!0,ref_key:"printableContent",ref:Z},[e[11]||(e[11]=t("div",{class:"bg-blue-800 text-white text-center py-2 font-bold"}," ELJIN CORPORATION ",-1)),t("div",Rt," PACKING LIST - "+a(d),1),e[12]||(e[12]=t("div",{class:"bg-blue-400 text-white text-center py-1"}," DELIVERY DATE: NOT POSTED ",-1)),t("div",St,[t("div",Lt,[e[8]||(e[8]=t("div",{class:"w-1/2 p-2 border-r border-gray-400"},"PRODUCT",-1)),t("div",kt,a(d),1),e[9]||(e[9]=t("div",{class:"w-1/4 p-2 text-center"},"ACTUAL",-1))]),t("div",Pt,[(n(!0),b(f,null,h(s,l=>(n(),b("div",{key:l.ITEMID,class:"flex"},[t("div",Ut,a(l.ITEMNAME),1),t("div",Mt,a(i(l.COUNTED)),1),t("div",$t,[H(t("input",{"onUpdate:modelValue":c=>l.ACTUAL=c,type:"number",class:"w-full text-center border border-gray-300 rounded",onInput:c=>X(d,l.ITEMNAME,l.ITEMID,c.target.value),disabled:!l.JOURNALID||!l.ITEMID,title:!l.JOURNALID||!l.ITEMID?"Cannot update: Missing required data":""},null,40,Gt),[[bt,l.ACTUAL]])])]))),128)),t("div",Vt,[e[10]||(e[10]=t("div",{class:"w-1/2 p-2 border-r border-gray-300"},"TOTAL",-1)),t("div",jt,a(i(q(s))),1),t("div",Ht,a(i(Q(s))),1)])])]),e[13]||(e[13]=t("div",{class:"max-w-md mx-auto border border-gray-300"},[t("table",{class:"w-full"},[t("tr",null,[t("td",{class:"border-b border-r border-gray-300 p-2 text-red-600 font-semibold"},"DISPATCHER:"),t("td",{class:"border-b border-gray-300 p-2"},[t("div",null,"SIGN OVER PRINTED NAME"),t("div",{class:"border-b border-gray-300 mt-4"})]),t("td",{class:"border-b border-l border-gray-300 p-2 text-sm text-right"},"NOT POSTED")]),t("tr",null,[t("td",{class:"border-r border-gray-300 p-2 font-semibold"},"LOGISTICS:"),t("td",{class:"p-2"},[t("div",null,"SIGN OVER PRINTED NAME"),t("div",{class:"border-b border-gray-300 mt-4"})]),t("td",{class:"border-l border-gray-300 p-2 text-sm text-right"},"NOT POSTED")])])],-1)),e[14]||(e[14]=t("br",null,null,-1))],512)]))),128))])])]),e[29]||(e[29]=t("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab","aria-label":"DR"},null,-1)),t("div",Ft,[$.value?(n(),b("div",Yt,e[15]||(e[15]=[t("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):E.value?(n(),b("div",Bt,[t("p",Kt,a(E.value),1)])):!I.value||Object.keys(I.value).length===0?(n(),b("div",zt,e[16]||(e[16]=[t("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[t("p",{class:"text-gray-600 text-base sm:text-lg"},"No DR List Available")],-1)]))):(n(!0),b(f,{key:3},h(I.value,(s,d)=>(n(),b("div",{key:d,class:"max-w-3xl mx-auto bg-gray-100 p-8 mb-8"},[e[26]||(e[26]=t("div",{class:"text-center mb-4"},[t("h1",{class:"font-bold"},"ELIIN CORPORATION"),t("p",null,"MALIWALO"),t("p",null,"TARLAC CITY"),t("h1",{class:"font-bold"},"DELIVERY GOODS RECEIPT: BW PRODUCTS")],-1)),t("div",Jt,[t("div",null,[t("p",null,"DR #: "+a(s[0].JOURNALID),1),e[17]||(e[17]=t("p",null,"DELIVERY DATE: NOT POSTED",-1))]),e[19]||(e[19]=t("div",null,[t("p",null,"RECIEVED FROM:"),t("p",null,"HEADOFFICE")],-1)),t("div",null,[e[18]||(e[18]=t("p",null,"DELIVERED TO:",-1)),t("p",null,a(d),1)])]),t("table",Wt,[e[25]||(e[25]=t("thead",null,[t("tr",{class:"bg-gray-200"},[t("th",{class:"border border-gray-300 p-2"},"PRODUCT DESCRIPTION"),t("th",{class:"border border-gray-300 p-2"},"TARGET"),t("th",{class:"border border-gray-300 p-2"},"ALLOC"),t("th",{class:"border border-gray-300 p-2"},"TOTAL"),t("th",{class:"border border-gray-300 p-2"},"RECEIVE QUANTITY"),t("th",{class:"border border-gray-300 p-2"},"TRANSFER COST"),t("th",{class:"border border-gray-300 p-2"},"TOTAL AMOUNT")])],-1)),t("tbody",null,[(n(!0),b(f,null,h(s,l=>(n(),b("tr",{key:l.ITEMID},[t("td",qt,a(l.ITEMNAME),1),t("td",Qt,a(i(l.CHECKINGCOUNT)),1),e[20]||(e[20]=t("td",{class:"border border-gray-300 p-2 text-center"},"0",-1)),t("td",Xt,a(i(Number(l.CHECKINGCOUNT))),1),e[21]||(e[21]=t("td",{class:"border border-gray-300 p-2 text-center"},null,-1)),t("td",Zt,a(g(l.COST)),1),t("td",te,a(g(Number(l.COST)*Number(l.CHECKINGCOUNT))),1)]))),128)),t("tr",ee,[e[22]||(e[22]=t("td",{class:"border border-gray-300 p-2"},"TOTAL",-1)),t("td",re,a(i(L(s))),1),e[23]||(e[23]=t("td",{class:"border border-gray-300 p-2 text-center"},"0",-1)),t("td",se,a(i(S(s))),1),e[24]||(e[24]=t("td",{class:"border border-gray-300 p-2 text-center"},null,-1)),t("td",oe,a(g(k(s))),1),t("td",le,a(g(P(s))),1)])])]),e[27]||(e[27]=t("div",{class:"mt-8 flex justify-between"},[t("div",null,[t("p",null,"ENDORSED BY: DISPATCHING"),t("p",null,"_____________________________"),t("p",null,"NAME & SIGNATURE / DATE")]),t("div",null,[t("p",null,"RECEIVED BY STORE"),t("p",null,"_____________________________"),t("p",null,"NAME & SIGNATURE / DATE")])],-1))]))),128))])])]),_:1})}}};export{Oe as default};
