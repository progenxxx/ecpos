import{C as G,Q as at,d as p,T as dt,q as V,p as nt,c as R,w as g,o as i,f as j,a as t,b as u,g as h,i as it,j as H,s as ct,u as N,e as b,h as E,t as n,F as v,n as bt,v as pt}from"./app-DIsI2h6N.js";import{_ as ut,a as gt}from"./Update-CqU256ny.js";import{_ as f}from"./Modal.vue_vue_type_style_index_0_scoped_fd08cd24_lang-BHRlHpU4.js";import{_ as yt}from"./TransparentButton-DJG8eOOC.js";import{S as Tt}from"./SearchColored-Cbq66vCU.js";import{B as mt}from"./Back--w-E6Kb2.js";import{F as _t}from"./File-BE4x0yoc.js";import{_ as ft}from"./AdminPanel-4mme3aov.js";import"./Modal-DKCN2m9N.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./InputError-CQ9k2kyq.js";import"./FormComponent-C5C7769U.js";import"./RetailGroup-g15s03XT.js";import"./Logout-CSswU4gJ.js";/* empty css                                                             */import"./RetailItems-BxtVv693.js";const ht={class:"absolute adjust"},Et={class:"flex justify-start items-center"},vt=["value"],xt={class:"mr-2"},Ot={role:"tablist",class:"tabs tabs-lifted mt-10 p-5"},Ct={role:"tabpanel",class:"tab-content bg-base-200 border-base-300 p-6 h-[70vh] overflow-y-auto"},Nt={class:"container mx-auto px-4"},At={class:"flex flex-wrap -mx-4"},It={key:0,class:"col-span-full text-center mt-8"},wt={key:1,class:"col-span-full text-center mt-8"},Dt={class:"text-red-600 text-lg"},Rt={key:2,class:"col-span-full text-center mt-8"},St={class:"bg-blue-600 text-white text-center py-1 font-semibold"},Lt={class:"bg-blue-400 text-white text-center py-1"},kt={class:"w-full px-4 mb-8"},Pt={class:"flex bg-gray-200 font-semibold"},Ut={class:"w-1/4 p-2 text-center border-r border-gray-400"},Mt={class:"divide-y divide-gray-300"},$t={class:"w-1/2 p-2 border-r border-gray-300"},Gt={class:"w-1/4 p-2 text-center border-r border-gray-300"},Vt={class:"w-1/4 p-2 text-center"},jt=["onUpdate:modelValue","onInput","disabled","title"],Ht={class:"flex bg-red-200"},Ft={class:"w-1/4 p-2 text-center border-r border-gray-300"},Yt={class:"w-1/4 p-2 text-center"},Kt={role:"tabpanel",class:"tab-content bg-base-100 border-base-200 p-6 h-[85vh] overflow-y-auto"},Bt={key:0,class:"col-span-full text-center mt-8"},zt={key:1,class:"col-span-full text-center mt-8"},Jt={class:"text-red-600 text-lg"},Wt={key:2,class:"col-span-full text-center mt-8"},qt={class:"flex justify-between mb-4"},Qt={class:"w-full border-collapse border border-gray-300"},Xt={class:"border border-gray-300 p-2"},Zt={class:"border border-gray-300 p-2 text-center"},te={class:"border border-gray-300 p-2 text-center"},ee={class:"border border-gray-300 p-2 text-right"},re={class:"border border-gray-300 p-2 text-right"},oe={class:"bg-gray-200 font-bold"},se={class:"border border-gray-300 p-2 text-center"},le={class:"border border-gray-300 p-2 text-center"},ae={class:"border border-gray-300 p-2 text-right"},de={class:"border border-gray-300 p-2 text-right"},Ce={__name:"cakes-old2",setup(ne){G.defaults.headers.common["X-CSRF-TOKEN"]=document.querySelector('meta[name="csrf-token"]').getAttribute("content");const A=at(),F=p(A.props.rbostoretables),m=p(Object.entries(A.props.groupedPicklist).reduce((r,[e,s])=>(r[e]=s.map(o=>({...o,actual:o.ACTUAL})),r),{})),I=p(Object.entries(A.props.groupedPicklist).reduce((r,[e,s])=>(r[e]=s.map(o=>({...o,actual:o.ACTUAL})),r),{})),Y=r=>new Date(r).toLocaleDateString(),T=r=>new Intl.NumberFormat("en-PH",{style:"currency",currency:"PHP"}).format(r),S=r=>r.reduce((e,s)=>e+Number(s.CHECKINGCOUNT||0),0),L=r=>r.reduce((e,s)=>e+Number(s.CHECKINGCOUNT||0),0),k=r=>r.reduce((e,s)=>e+Number(s.COST||0),0),P=r=>r.reduce((e,s)=>e+Number(s.COST||0)*Number(s.CHECKINGCOUNT||0),0),K=p(""),B=p(""),z=p(""),U=p(!1),M=p(!1),$=p(!1),x=p(null),_=dt({STORE:""}),J=()=>{_.get(route("cakepicklist.getstore"),{preserveScroll:!0})};p(null),V(()=>{const r=_.StartDate;if(r){const e=new Date(r),s=e.getFullYear(),o=String(e.getMonth()+1).padStart(2,"0"),l=String(e.getDate()).padStart(2,"0");return`${s}-${o}-${l}`}return""}),p(null),V(()=>{const r=_.EndDate;if(r){const e=new Date(r),s=e.getFullYear(),o=String(e.getMonth()+1).padStart(2,"0"),l=String(e.getDate()).padStart(2,"0");return`${s}-${o}-${l}`}return""});const W=()=>{U.value=!1},q=()=>{M.value=!1},Q=r=>r.reduce((e,s)=>e+parseFloat(s.COUNTED||0),0),O=(r,e)=>r.reduce((s,o)=>s+(o[e]||0),0),X=r=>r.reduce((e,s)=>e+parseFloat(s.actual||0),0),c=r=>{const e=parseFloat(r);return Number.isInteger(e)?e.toString():Math.round(e).toString()},Z=async(r,e,s,o)=>{try{const d=m.value[r].find(y=>y.ITEMID===s);if(!d){console.error("Item not found");return}if(!d.JOURNALID){console.error("JOURNALID is missing for this item");return}const a=await G.post("/api/update-actual",{journal_id:d.JOURNALID,store_name:r,item_name:e,item_id:s,actual:o});a.data.success?d.ACTUAL=o:console.error("Failed to update ACTUAL value",a.data)}catch(l){l.response&&l.response.data?(console.error("Server validation errors:",l.response.data.errors),Object.entries(l.response.data.errors).forEach(([d,a])=>{console.error(`${d}: ${a.join(", ")}`)})):console.error("Error updating ACTUAL value:",l.message)}},tt=p(null),et=()=>{window.location.href="/picklist"},rt=()=>{window.location.href="/mgcount"},ot=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),e=Object.entries(m.value);let s="";for(let o=0;o<e.length;o+=2){const l=e.slice(o,o+2).map(([d,a])=>{const y=a.map(C=>`
        <tr>
          <td class="border p-1">${C.ITEMNAME}</td>
          <td class="border p-1 text-center">${c(C.COUNTED)}</td>
          <td class="border p-1 text-center"></td>
        </tr>
      `).join(""),w=O(a,"COUNTED");return O(a,"ACTUAL"),`
        <div class="store-section">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST - ${d}</div>
          <div class="bg-blue-400">DELIVERY DATE: NOT POSTED</div>
          <table>
            <thead>
              <tr>
                <th class="border p-1">PRODUCT</th>
                <th class="border p-1">${d}</th>
                <th class="border p-1">ACTUAL</th>
              </tr>
            </thead>
            <tbody>
              ${y}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${c(w)}</td>
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
  `),r.document.close(),r.focus(),r.print(),r.close()},st=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),e=Object.entries(m.value);let s="";for(let o=0;o<e.length;o+=2){const l=e.slice(o,o+2).map(([d,a])=>{const y=a.map(D=>`
        <tr>
          <td class="border p-1">${D.ITEMNAME}</td>
          <td class="border p-1 text-center">${c(D.COUNTED)}</td>
          <td class="border p-1 text-center">${c(D.CHECKINGCOUNT)}</td>
        </tr>
      `).join(""),w=O(a,"COUNTED"),C=O(a,"ACTUAL");return`
        <div class="store-section">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST - ${d}</div>
          <div class="bg-blue-400">DELIVERY DATE: NOT POSTED</div>
          <table>
            <thead>
              <tr>
                <th class="border p-1">PRODUCT</th>
                <th class="border p-1">${d}</th>
                <th class="border p-1">ACTUAL</th>
              </tr>
            </thead>
            <tbody>
              ${y}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${c(w)}</td>
                <td class="border p-1 text-center font-bold">${c(C)}</td>
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
  `),r.document.close(),r.focus(),r.print(),r.close()},lt=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0");let e="";for(const[s,o]of Object.entries(m.value))e+=`
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
            <p>DELIVERY DATE: NOT POSTED</p>
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
                <td class="border border-gray-300 p-2 text-center">${c(l.CHECKINGCOUNT)}</td>
                <td class="border border-gray-300 p-2 text-center">0</td>
                <td class="border border-gray-300 p-2 text-center">${c(Number(l.CHECKINGCOUNT))}</td>
                <td class="border border-gray-300 p-2 text-center"></td>
                <td class="border border-gray-300 p-2 text-right">${T(l.COST)}</td>
                <td class="border border-gray-300 p-2 text-right">${T(Number(l.COST)*Number(l.CHECKINGCOUNT))}</td>
              </tr>
            `).join("")}
            <tr class="bg-gray-200 font-bold">
              <td class="border border-gray-300 p-2">TOTAL</td>
              <td class="border border-gray-300 p-2 text-center">${c(L(o))}</td>
              <td class="border border-gray-300 p-2 text-center">0</td>
              <td class="border border-gray-300 p-2 text-center">${c(S(o))}</td>
              <td class="border border-gray-300 p-2 text-center"></td>
              <td class="border border-gray-300 p-2 text-right">${T(k(o))}</td>
              <td class="border border-gray-300 p-2 text-right">${T(P(o))}</td>
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
  `),r.document.close(),r.focus(),r.print(),r.close()};return(r,e)=>{const s=nt("InputLabel");return i(),R(ft,{"active-tab":"FGCOUNT"},{modals:g(()=>[M.value?(i(),R(ut,{key:0,onToggleActive:q})):j("",!0),U.value?(i(),R(gt,{key:1,ID:K.value,SUBJECT:B.value,DESCRIPTION:z.value,onToggleActive:W},null,8,["ID","SUBJECT","DESCRIPTION"])):j("",!0)]),main:g(()=>[t("div",ht,[t("div",Et,[u(f,{type:"button",onClick:rt,class:"m-1 ml-2 bg-navy p-10 mt-4"},{default:g(()=>[u(mt,{class:"h-5"})]),_:1}),u(f,{type:"button",onClick:ot,class:"bg-navy tooltip tooltip-right tooltip-primary mt-4","data-tip":"Packing List Template"},{default:g(()=>[u(_t,{class:"h-5"})]),_:1}),u(f,{type:"button",onClick:st,class:"ml-1 mt-4 bg-navy"},{default:g(()=>e[1]||(e[1]=[h(" PRINT PL ")])),_:1}),u(f,{type:"button",onClick:lt,class:"ml-1 mt-4 bg-navy"},{default:g(()=>e[2]||(e[2]=[h(" PRINT DR ")])),_:1}),u(f,{type:"button",onClick:et,class:"ml-2 bg-navy mt-4"},{default:g(()=>e[3]||(e[3]=[h(" CALCULATE ")])),_:1}),u(f,{type:"button",onClick:r.specialorder,class:"ml-2 bg-navy mt-4"},{default:g(()=>e[4]||(e[4]=[h(" SPECIAL ORDER ")])),_:1},8,["onClick"]),u(f,{type:"button",onClick:r.inputpartycakes,class:"ml-2 bg-navy mt-4"},{default:g(()=>e[5]||(e[5]=[h(" PARTYCAKES ")])),_:1},8,["onClick"]),e[7]||(e[7]=t("details",{className:"dropdown"},[t("summary",{className:"btn m-1 mt-4  bg-green-900 text-white hover:bg-navy"},"Select Route"),t("ul",{className:"menu dropdown-content bg-base-100 rounded-box z-[1] w-52 p-2 shadow"},[t("li",null,[t("a",{href:"/cakepicklist"},"ALL")]),t("li",null,[t("a",{href:"/plc-south1"},"SOUTH 1")]),t("li",null,[t("a",{href:"/plc-south2"},"SOUTH 2")]),t("li",null,[t("a",{href:"/plc-south3"},"SOUTH 3")]),t("li",null,[t("a",{href:"/plc-north1"},"NORTH 1")]),t("li",null,[t("a",{href:"/plc-north2"},"NORTH 2")]),t("li",null,[t("a",{href:"/plc-central"},"CENTRAL")]),t("li",null,[t("a",{href:"/plc-east"},"EAST")])])],-1)),t("form",{onSubmit:it(J,["prevent"]),class:"flex items-center mt-4"},[t("input",{type:"hidden",name:"_token",value:r.$page.props.csrf_token},null,8,vt),t("div",xt,[u(s,{for:"STORE",value:"STORE",class:"sr-only"}),H(t("select",{id:"STORE","onUpdate:modelValue":e[0]||(e[0]=o=>N(_).STORE=o),class:"input input-bordered w-64"},[e[6]||(e[6]=t("option",{disabled:"",value:""},"Select Store",-1)),(i(!0),b(v,null,E(F.value,o=>(i(),b("option",{key:o.STOREID},n(o.NAME),1))),128))],512),[[ct,N(_).STORE]])]),u(yt,{type:"submit",disabled:N(_).processing,class:bt({"opacity-25":N(_).processing})},{default:g(()=>[u(Tt,{class:"h-8"})]),_:1},8,["disabled","class"])],32)])]),t("div",Ot,[e[29]||(e[29]=t("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab bg-base-200 border-base-300","aria-label":"PICK LIST",checked:""},null,-1)),t("div",Ct,[t("div",Nt,[t("div",At,[$.value?(i(),b("div",It,e[8]||(e[8]=[t("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):x.value?(i(),b("div",wt,[t("p",Dt,n(x.value),1)])):!m.value||Object.keys(m.value).length===0?(i(),b("div",Rt,e[9]||(e[9]=[t("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[t("p",{class:"text-gray-600 text-base sm:text-lg"},"No Pick List Available")],-1)]))):(i(!0),b(v,{key:3},E(m.value,(o,l)=>{var d;return i(),b("div",{key:l,class:"w-full mb-8"},[t("div",{class:"max-w-xl mx-auto bg-white shadow-lg",ref_for:!0,ref_key:"printableContent",ref:tt},[e[13]||(e[13]=t("div",{class:"bg-blue-800 text-white text-center py-2 font-bold"}," ELJIN CORPORATION ",-1)),t("div",St," PACKING LIST - "+n(l),1),t("div",Lt," DELIVERY DATE: "+n(Y((d=o[0])==null?void 0:d.DELIVERYDATE)||"Not available"),1),t("div",kt,[t("div",Pt,[e[10]||(e[10]=t("div",{class:"w-1/2 p-2 border-r border-gray-400"},"PRODUCT",-1)),t("div",Ut,n(l),1),e[11]||(e[11]=t("div",{class:"w-1/4 p-2 text-center"},"ACTUAL",-1))]),t("div",Mt,[(i(!0),b(v,null,E(o,a=>(i(),b("div",{key:a.ITEMID,class:"flex"},[t("div",$t,n(a.ITEMNAME),1),t("div",Gt,n(c(a.COUNTED)),1),t("div",Vt,[H(t("input",{"onUpdate:modelValue":y=>a.ACTUAL=y,type:"number",class:"w-full text-center border border-gray-300 rounded",onInput:y=>Z(l,a.ITEMNAME,a.ITEMID,y.target.value),disabled:!a.JOURNALID||!a.ITEMID,title:!a.JOURNALID||!a.ITEMID?"Cannot update: Missing required data":""},null,40,jt),[[pt,a.ACTUAL]])])]))),128)),t("div",Ht,[e[12]||(e[12]=t("div",{class:"w-1/2 p-2 border-r border-gray-300"},"TOTAL",-1)),t("div",Ft,n(c(Q(o))),1),t("div",Yt,n(c(X(o))),1)])])]),e[14]||(e[14]=t("div",{class:"max-w-md mx-auto border border-gray-300"},[t("table",{class:"w-full"},[t("tr",null,[t("td",{class:"border-b border-r border-gray-300 p-2 text-red-600 font-semibold"},"DISPATCHER:"),t("td",{class:"border-b border-gray-300 p-2"},[t("div",null,"SIGN OVER PRINTED NAME"),t("div",{class:"border-b border-gray-300 mt-4"})]),t("td",{class:"border-b border-l border-gray-300 p-2 text-sm text-right"},"NOT POSTED")]),t("tr",null,[t("td",{class:"border-r border-gray-300 p-2 font-semibold"},"LOGISTICS:"),t("td",{class:"p-2"},[t("div",null,"SIGN OVER PRINTED NAME"),t("div",{class:"border-b border-gray-300 mt-4"})]),t("td",{class:"border-l border-gray-300 p-2 text-sm text-right"},"NOT POSTED")])])],-1)),e[15]||(e[15]=t("br",null,null,-1))],512)])}),128))])])]),e[30]||(e[30]=t("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab","aria-label":"DR"},null,-1)),t("div",Kt,[$.value?(i(),b("div",Bt,e[16]||(e[16]=[t("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):x.value?(i(),b("div",zt,[t("p",Jt,n(x.value),1)])):!I.value||Object.keys(I.value).length===0?(i(),b("div",Wt,e[17]||(e[17]=[t("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[t("p",{class:"text-gray-600 text-base sm:text-lg"},"No DR List Available")],-1)]))):(i(!0),b(v,{key:3},E(I.value,(o,l)=>(i(),b("div",{key:l,class:"max-w-3xl mx-auto bg-gray-100 p-8 mb-8"},[e[27]||(e[27]=t("div",{class:"text-center mb-4"},[t("h1",{class:"font-bold"},"ELIIN CORPORATION"),t("p",null,"MALIWALO"),t("p",null,"TARLAC CITY"),t("h1",{class:"font-bold"},"DELIVERY GOODS RECEIPT: BW PRODUCTS")],-1)),t("div",qt,[t("div",null,[t("p",null,"DR #: "+n(o[0].JOURNALID),1),e[18]||(e[18]=t("p",null,"DELIVERY DATE: NOT POSTED",-1))]),e[20]||(e[20]=t("div",null,[t("p",null,"RECIEVED FROM:"),t("p",null,"HEADOFFICE")],-1)),t("div",null,[e[19]||(e[19]=t("p",null,"DELIVERED TO:",-1)),t("p",null,n(l),1)])]),t("table",Qt,[e[26]||(e[26]=t("thead",null,[t("tr",{class:"bg-gray-200"},[t("th",{class:"border border-gray-300 p-2"},"PRODUCT DESCRIPTION"),t("th",{class:"border border-gray-300 p-2"},"TARGET"),t("th",{class:"border border-gray-300 p-2"},"ALLOC"),t("th",{class:"border border-gray-300 p-2"},"TOTAL"),t("th",{class:"border border-gray-300 p-2"},"RECEIVE QUANTITY"),t("th",{class:"border border-gray-300 p-2"},"TRANSFER COST"),t("th",{class:"border border-gray-300 p-2"},"TOTAL AMOUNT")])],-1)),t("tbody",null,[(i(!0),b(v,null,E(o,d=>(i(),b("tr",{key:d.ITEMID},[t("td",Xt,n(d.ITEMNAME),1),t("td",Zt,n(c(d.CHECKINGCOUNT)),1),e[21]||(e[21]=t("td",{class:"border border-gray-300 p-2 text-center"},"0",-1)),t("td",te,n(c(Number(d.CHECKINGCOUNT))),1),e[22]||(e[22]=t("td",{class:"border border-gray-300 p-2 text-center"},null,-1)),t("td",ee,n(T(d.COST)),1),t("td",re,n(T(Number(d.COST)*Number(d.CHECKINGCOUNT))),1)]))),128)),t("tr",oe,[e[23]||(e[23]=t("td",{class:"border border-gray-300 p-2"},"TOTAL",-1)),t("td",se,n(c(L(o))),1),e[24]||(e[24]=t("td",{class:"border border-gray-300 p-2 text-center"},"0",-1)),t("td",le,n(c(S(o))),1),e[25]||(e[25]=t("td",{class:"border border-gray-300 p-2 text-center"},null,-1)),t("td",ae,n(T(k(o))),1),t("td",de,n(T(P(o))),1)])])]),e[28]||(e[28]=t("div",{class:"mt-8 flex justify-between"},[t("div",null,[t("p",null,"ENDORSED BY: DISPATCHING"),t("p",null,"_____________________________"),t("p",null,"NAME & SIGNATURE / DATE")]),t("div",null,[t("p",null,"RECEIVED BY STORE"),t("p",null,"_____________________________"),t("p",null,"NAME & SIGNATURE / DATE")])],-1))]))),128))])])]),_:1})}}};export{Ce as default};
