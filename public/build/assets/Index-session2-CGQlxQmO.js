import{D as M,Q as nt,d as _,T as ct,s as P,p as bt,c as O,w as T,o as n,f as $,a as t,b as u,g as i,i as it,j as V,v as G,u as m,n as pt,e as c,t as d,h,F as A}from"./app-Brw7jJEf.js";import{_ as _t,a as gt}from"./Update-CsRCIOoj.js";import{_ as I}from"./Modal.vue_vue_type_style_index_0_scoped_fd08cd24_lang-DaotuncJ.js";import{_ as ut}from"./TransparentButton-C3Epfz9M.js";import{S as Tt}from"./SearchColored-Cf555PA_.js";import{B as yt}from"./Back-BKwDRjC6.js";import{_ as Et}from"./AdminPanel-gBVzbbkn.js";import"./Modal-CtatsLVe.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./InputError-DvOIMu0q.js";import"./FormComponent-BslV3y3C.js";import"./RetailGroup-CBJ5tnzv.js";import"./Logout-DCQOxVUr.js";/* empty css                                                             */import"./RetailItems-Bli-UwjH.js";import"./Attendance-DYGT2FcC.js";const xt={class:"absolute adjust"},ft={class:"flex justify-start items-center"},mt=["value"],vt={"date-rangepicker":"",class:"flex items-center"},ht={class:"relative ml-5"},At=["placeholder"],It={role:"tablist",class:"tabs tabs-lifted mt-10 p-5"},Ct={role:"tabpanel",class:"tab-content bg-base-200 border-base-300 p-6 h-[70vh] overflow-y-auto"},Dt={class:"container mx-auto px-4"},Nt={class:"flex flex-wrap -mx-4"},Rt={key:0,class:"col-span-full text-center mt-8"},Ot={key:1,class:"col-span-full text-center mt-8"},wt={class:"text-red-600 text-lg"},St={key:2,class:"col-span-full text-center mt-8"},Lt={class:"bg-blue-600 text-white text-center py-1 font-semibold"},Ut={class:"bg-blue-400 text-white text-center py-1"},kt={class:"w-full px-4 mb-8"},Mt={class:"flex bg-gray-200 font-semibold"},Pt={class:"w-1/4 p-2 text-center border-r border-gray-400"},$t={class:"divide-y divide-gray-300"},Vt={class:"w-1/2 p-2 border-r border-gray-300"},Gt={class:"w-1/4 p-2 text-center border-r border-gray-300"},Yt={class:"w-1/4 p-2 text-center"},jt=["onUpdate:modelValue","onInput","disabled","title"],Ft={class:"flex bg-red-200"},Bt={class:"w-1/4 p-2 text-center border-r border-gray-300"},Ht={class:"w-1/4 p-2 text-center"},Jt={role:"tabpanel",class:"tab-content bg-base-100 border-base-200 p-6 h-[85vh] overflow-y-auto"},Kt={key:0,class:"col-span-full text-center mt-8"},zt={key:1,class:"col-span-full text-center mt-8"},Qt={class:"text-red-600 text-lg"},Wt={key:2,class:"col-span-full text-center mt-8"},qt={class:"flex justify-between mb-4"},Xt={class:"w-full border-collapse border border-gray-300"},Zt={class:"border border-gray-300 p-2"},te={class:"border border-gray-300 p-2 text-center"},ee={class:"border border-gray-300 p-2 text-center"},re={class:"border border-gray-300 p-2 text-center"},se={class:"border border-gray-300 p-2 text-right"},oe={class:"border border-gray-300 p-2 text-right"},ae={class:"bg-gray-200 font-bold"},le={class:"border border-gray-300 p-2 text-center"},de={class:"border border-gray-300 p-2 text-center"},ne={class:"border border-gray-300 p-2 text-center"},ce={class:"border border-gray-300 p-2 text-right"},be={class:"border border-gray-300 p-2 text-right"},ie={role:"tabpanel",class:"tab-content bg-base-100 border-base-200 p-6 h-[85vh] overflow-y-auto"},pe={key:0,class:"col-span-full text-center mt-8"},_e={key:1,class:"col-span-full text-center mt-8"},ge={class:"text-red-600 text-lg"},ue={key:2,class:"col-span-full text-center mt-8"},Te={key:3,class:"max-w-4xl mx-auto p-4 bg-white shadow-lg text-xs"},Ue={__name:"Index-session2",setup(ye){M.defaults.headers.common["X-CSRF-TOKEN"]=document.querySelector('meta[name="csrf-token"]').getAttribute("content");const w=nt(),y=_(Object.entries(w.props.groupedPicklist).reduce((r,[e,o])=>(r[e]=o.map(s=>({...s,actual:s.ACTUAL})),r),{})),x=_(Object.entries(w.props.groupedPicklist).reduce((r,[e,o])=>(r[e]=o.map(s=>({...s,actual:s.ACTUAL})),r),{})),Y=r=>new Date(r).toLocaleDateString(),f=r=>new Intl.NumberFormat("en-PH",{style:"currency",currency:"PHP"}).format(r),j=r=>r.reduce((e,o)=>e+Number(o.TARGET||0),0),F=r=>r.reduce((e,o)=>e+Number(o.CHECKINGCOUNT||0),0),B=r=>r.reduce((e,o)=>e+(Number(o.CHECKINGCOUNT||0)-Number(o.TARGET||0)),0),H=r=>r.reduce((e,o)=>e+Number(o.COST||0),0),J=r=>r.reduce((e,o)=>e+Number(o.CHECKINGCOUNT||0)*Number(o.COST||0),0),K=_(""),z=_(""),Q=_(""),S=_(!1),L=_(!1),C=_(!1),E=_(null),g=ct({StartDate:"2024-07-22",StoreName:"Urdaneta2"}),U=()=>{g.get(route("picklist.getrange"),{preserveScroll:!0})};_(null);const D=P(()=>{const r=g.StartDate;if(r){const e=new Date(r),o=e.getFullYear(),s=String(e.getMonth()+1).padStart(2,"0"),l=String(e.getDate()).padStart(2,"0");return`${o}-${s}-${l}`}return""});_(null),P(()=>{const r=g.EndDate;if(r){const e=new Date(r),o=e.getFullYear(),s=String(e.getMonth()+1).padStart(2,"0"),l=String(e.getDate()).padStart(2,"0");return`${o}-${s}-${l}`}return""});const W=()=>{S.value=!1},q=()=>{L.value=!1},X=r=>r.reduce((e,o)=>e+parseFloat(o.COUNTED||0),0),k=(r,e)=>r.reduce((o,s)=>o+(s[e]||0),0),Z=r=>r.reduce((e,o)=>e+parseFloat(o.actual||0),0),v=()=>{const r=new Date;return`${r.getMonth()+1}/${r.getDate()}/${r.getFullYear().toString().substr(-2)}`},b=r=>{const e=parseFloat(r);return Number.isInteger(e)?e.toString():Math.round(e).toString()},tt=async(r,e,o,s)=>{try{const a=y.value[r].find(N=>N.ITEMID===o);if(!a){console.error("Item not found");return}if(!a.JOURNALID){console.error("JOURNALID is missing for this item");return}const p=await M.post("/api/update-actual",{journal_id:a.JOURNALID,store_name:r,item_name:e,item_id:o,actual:s});p.data.success?a.ACTUAL=s:console.error("Failed to update ACTUAL value",p.data)}catch(l){l.response&&l.response.data?(console.error("Server validation errors:",l.response.data.errors),Object.entries(l.response.data.errors).forEach(([a,p])=>{console.error(`${a}: ${p.join(", ")}`)})):console.error("Error updating ACTUAL value:",l.message)}},et=_(null),rt=()=>{window.location.href="/mgcount"},st=()=>{window.location.href="/picklist"},ot=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),e=Object.entries(y.value);let o="";for(let s=0;s<e.length;s+=2){const l=e.slice(s,s+2).map(([a,p])=>{const N=p.map(R=>`
        <tr>
          <td class="border p-1">${R.ITEMNAME}</td>
          <td class="border p-1 text-center">${b(R.COUNTED)}</td>
          <td class="border p-1 text-center">${b(R.ACTUAL)}</td>
        </tr>
      `).join(""),lt=k(p,"COUNTED"),dt=k(p,"ACTUAL");return`
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
              ${N}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${b(lt)}</td>
                <td class="border p-1 text-center font-bold">${b(dt)}</td>
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
      `}).join("");o+=`
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
            font-size: 10px;
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
            font-size: 10px; 
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
  `),r.document.close(),r.focus(),r.print(),r.close()},at=()=>{const r=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0");let e="";for(const[o,s]of Object.entries(y.value))e+=`
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
            <p>DELIVERY DATE: ${Y(s[0].POSTEDDATETIME)}</p>
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
            ${s.map(l=>`
              <tr>
                <td class="border border-gray-300 p-2">${l.ITEMNAME}</td>
                <td class="border border-gray-300 p-2 text-center">${b(l.COUNTED)}</td>
                <td class="border border-gray-300 p-2 text-center">${l.ADJUSTMENT}</td>
                <td class="border border-gray-300 p-2 text-center">${b(Number(l.COUNTED)+Number(l.ADJUSTMENT))}</td>
                <td class="border border-gray-300 p-2 text-center"></td>
                <td class="border border-gray-300 p-2 text-right">${f(l.COST)}</td>
                <td class="border border-gray-300 p-2 text-right">TEST</td>
              </tr>
            `).join("")}
            <tr class="bg-gray-200 font-bold">
              <td class="border border-gray-300 p-2">TOTAL</td>
              <td class="border border-gray-300 p-2 text-center">${b(calculateTotalCounted(s))}</td>
              <td class="border border-gray-300 p-2 text-center">${b(calculateTotalAdjustment(s))}</td>
              <td class="border border-gray-300 p-2 text-center">${b(calculateTotalCountedPlusAdjustment(s))}</td>
              <td class="border border-gray-300 p-2 text-center"></td>
              <td class="border border-gray-300 p-2 text-right"></td>
              <td class="border border-gray-300 p-2 text-right">TEST</td>
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
            font-size: 10px;
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
            font-size: 10px; 
          }
          .text-center { text-align: center; }
          .text-right { text-align: right; }
          .font-bold { font-weight: bold; }
          .mb-4 { margin-bottom: 16px; }
          .mt-8 { margin-top: 32px; }
          .flex { display: flex; }
          .justify-between { justify-content: space-between; }
          .bg-gray-200 { background-color: #edf2f7; }
        </style>
      </head>
      <body>
        ${e}
      </body>
    </html>
  `),r.document.close(),r.focus(),r.print(),r.close()};return(r,e)=>{const o=bt("InputError");return n(),O(Et,{"active-tab":"PICKLIST"},{modals:T(()=>[L.value?(n(),O(_t,{key:0,onToggleActive:q})):$("",!0),S.value?(n(),O(gt,{key:1,ID:K.value,SUBJECT:z.value,DESCRIPTION:Q.value,onToggleActive:W},null,8,["ID","SUBJECT","DESCRIPTION"])):$("",!0)]),main:T(()=>[t("div",xt,[t("div",ft,[u(I,{type:"button",onClick:rt,class:"m-1 ml-2 bg-navy p-10"},{default:T(()=>[u(yt,{class:"h-5"})]),_:1}),u(I,{type:"button",onClick:ot,class:"m-6 bg-navy"},{default:T(()=>e[2]||(e[2]=[i(" PRINT PL ")])),_:1}),u(I,{type:"button",onClick:at,class:"bg-navy"},{default:T(()=>e[3]||(e[3]=[i(" PRINT DR ")])),_:1}),u(I,{type:"button",onClick:st,class:"ml-2 bg-navy"},{default:T(()=>e[4]||(e[4]=[i(" CALCULATE ")])),_:1}),t("form",{onSubmit:it(U,["prevent"]),class:"px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto"},[t("input",{type:"hidden",name:"_token",value:r.$page.props.csrf_token},null,8,mt),t("div",vt,[t("div",ht,[e[5]||(e[5]=t("div",{class:"flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none"},[t("svg",{class:"w-5 h-5 text-gray-500 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[t("path",{"fill-rule":"evenodd",d:"M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z","clip-rule":"evenodd"})])],-1)),V(t("input",{id:"StartDate",type:"date","onUpdate:modelValue":e[0]||(e[0]=s=>m(g).StartDate=s),onInput:e[1]||(e[1]=(...s)=>D.value&&D.value(...s)),placeholder:D.value,class:"bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",required:""},null,40,At),[[G,m(g).StartDate]]),u(o,{message:m(g).errors.StartDate,class:"mt-2"},null,8,["message"])])])],32),u(ut,{type:"submit",onClick:U,disabled:m(g).processing,class:pt({"opacity-25":m(g).processing})},{default:T(()=>[u(Tt,{class:"h-8"})]),_:1},8,["disabled","class"])])]),t("div",It,[e[27]||(e[27]=t("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab bg-base-200 border-base-300","aria-label":"PICK LIST",checked:""},null,-1)),t("div",Ct,[t("div",Dt,[t("div",Nt,[C.value?(n(),c("div",Rt,e[6]||(e[6]=[t("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):E.value?(n(),c("div",Ot,[t("p",wt,d(E.value),1)])):!y.value||Object.keys(y.value).length===0?(n(),c("div",St,e[7]||(e[7]=[t("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[t("p",{class:"text-gray-600 text-base sm:text-lg"},"No Pick List Available")],-1)]))):(n(!0),c(A,{key:3},h(y.value,(s,l)=>(n(),c("div",{key:l,class:"w-full mb-8"},[t("div",{class:"max-w-xl mx-auto bg-white shadow-lg",ref_for:!0,ref_key:"printableContent",ref:et},[e[11]||(e[11]=t("div",{class:"bg-blue-800 text-white text-center py-2 font-bold"}," ELJIN CORPORATION ",-1)),t("div",Lt," PACKING LIST - "+d(l),1),t("div",Ut," DELIVERY DATE: "+d(v()),1),t("div",kt,[t("div",Mt,[e[8]||(e[8]=t("div",{class:"w-1/2 p-2 border-r border-gray-400"},"PRODUCT",-1)),t("div",Pt,d(l),1),e[9]||(e[9]=t("div",{class:"w-1/4 p-2 text-center"},"ACTUAL",-1))]),t("div",$t,[(n(!0),c(A,null,h(s,a=>(n(),c("div",{key:a.ITEMID,class:"flex"},[t("div",Vt,d(a.ITEMNAME),1),t("div",Gt,d(b(a.COUNTED)),1),t("div",Yt,[V(t("input",{"onUpdate:modelValue":p=>a.ACTUAL=p,type:"number",class:"w-full text-center border border-gray-300 rounded",onInput:p=>tt(l,a.ITEMNAME,a.ITEMID,p.target.value),disabled:!a.JOURNALID||!a.ITEMID,title:!a.JOURNALID||!a.ITEMID?"Cannot update: Missing required data":""},null,40,jt),[[G,a.ACTUAL]])])]))),128)),t("div",Ft,[e[10]||(e[10]=t("div",{class:"w-1/2 p-2 border-r border-gray-300"},"TOTAL",-1)),t("div",Bt,d(b(X(s))),1),t("div",Ht,d(b(Z(s))),1)])])]),e[12]||(e[12]=t("br",null,null,-1))],512)]))),128))])])]),e[28]||(e[28]=t("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab","aria-label":"DR1"},null,-1)),t("div",Jt,[C.value?(n(),c("div",Kt,e[13]||(e[13]=[t("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):E.value?(n(),c("div",zt,[t("p",Qt,d(E.value),1)])):!x.value||Object.keys(x.value).length===0?(n(),c("div",Wt,e[14]||(e[14]=[t("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[t("p",{class:"text-gray-600 text-base sm:text-lg"},"No DR1 List Available")],-1)]))):(n(!0),c(A,{key:3},h(x.value,(s,l)=>(n(),c("div",{key:l,class:"max-w-3xl mx-auto bg-gray-100 p-8 mb-8"},[e[22]||(e[22]=t("div",{class:"text-center mb-4"},[t("h1",{class:"font-bold"},"ELIIN CORPORATION"),t("p",null,"MALIWALO"),t("p",null,"TARLAC CITY"),t("h1",{class:"font-bold"},"DELIVERY GOODS RECEIPT: BW PRODUCTS")],-1)),t("div",qt,[t("div",null,[t("p",null,"DR #: "+d(s[0].JOURNALID),1),e[15]||(e[15]=t("p",null,"DELIVERY DATE: ___________________________",-1))]),e[17]||(e[17]=t("div",null,[t("p",null,"RECIEVED FROM:"),t("p",null,"HEADOFFICE")],-1)),t("div",null,[e[16]||(e[16]=t("p",null,"DELIVERED TO:",-1)),t("p",null,d(l),1)])]),t("table",Xt,[e[21]||(e[21]=t("thead",null,[t("tr",{class:"bg-gray-200"},[t("th",{class:"border border-gray-300 p-2"},"PRODUCT DESCRIPTION"),t("th",{class:"border border-gray-300 p-2"},"TARGET"),t("th",{class:"border border-gray-300 p-2"},"ALLOC"),t("th",{class:"border border-gray-300 p-2"},"VARIANCE"),t("th",{class:"border border-gray-300 p-2"},"RECEIVE QUANTITY"),t("th",{class:"border border-gray-300 p-2"},"TRANSFER COST"),t("th",{class:"border border-gray-300 p-2"},"TOTAL AMOUNT")])],-1)),t("tbody",null,[(n(!0),c(A,null,h(s,a=>(n(),c("tr",{key:a.ITEMID},[t("td",Zt,d(a.ITEMNAME),1),t("td",te,d(b(a.TARGET)),1),t("td",ee,d(a.CHECKINGCOUNT),1),t("td",re,d(b(Number(a.CHECKINGCOUNT)-Number(a.TARGET))),1),e[18]||(e[18]=t("td",{class:"border border-gray-300 p-2 text-center"},null,-1)),t("td",se,d(f(a.COST)),1),t("td",oe,d(f(Number(a.COST)*Number(a.CHECKINGCOUNT))),1)]))),128)),t("tr",ae,[e[19]||(e[19]=t("td",{class:"border border-gray-300 p-2"},"TOTAL",-1)),t("td",le,d(b(j(s))),1),t("td",de,d(b(F(s))),1),t("td",ne,d(b(B(s))),1),e[20]||(e[20]=t("td",{class:"border border-gray-300 p-2 text-center"},null,-1)),t("td",ce,d(f(H(s))),1),t("td",be,d(f(J(s))),1)])])]),e[23]||(e[23]=t("div",{class:"mt-8 flex justify-between"},[t("div",null,[t("p",null,"ENDORSED BY: DISPATCHING"),t("p",null,"_____________________________"),t("p",null,"NAME & SIGNATURE / DATE")]),t("div",null,[t("p",null,"RECEIVED BY STORE"),t("p",null,"_____________________________"),t("p",null,"NAME & SIGNATURE / DATE")])],-1))]))),128))]),e[29]||(e[29]=t("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab","aria-label":"DR2"},null,-1)),t("div",ie,[C.value?(n(),c("div",pe,e[24]||(e[24]=[t("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):E.value?(n(),c("div",_e,[t("p",ge,d(E.value),1)])):!x.value||Object.keys(x.value).length===0?(n(),c("div",ue,e[25]||(e[25]=[t("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[t("p",{class:"text-gray-600 text-base sm:text-lg"},"No DR2 List Available")],-1)]))):(n(),c("div",Te,e[26]||(e[26]=[t("table",{class:"w-full border-collapse border border-gray-400"},[t("tr",null,[t("td",{colspan:"6",class:"text-center font-bold text-lg border border-gray-400 p-1"},[i(" MALIWALO"),t("br"),i(" TARLAC CITY ")])]),t("tr",null,[t("td",{colspan:"4",class:"font-bold border border-gray-400 p-1"},[i("DELIVERY GOODS RECEIPT"),t("br"),i("BW PRODUCT")]),t("td",{colspan:"2",class:"border border-gray-400 p-1"},[i(" DR #: "),t("span",{class:"font-bold"},"005258"),t("br"),i(" DELIVERY DATE: _________________________ ")])]),t("tr",null,[t("td",{colspan:"3",class:"border border-gray-400 p-1"},[i("RECEIVED FROM:"),t("br"),t("span",{class:"font-bold"},"HEADOFFICE")]),t("td",{colspan:"3",class:"border border-gray-400 p-1"},[i("DELIVERED TO:"),t("br"),t("span",{class:"font-bold"},"CAMACHILLES")])]),t("tr",{class:"bg-gray-200 font-bold"},[t("td",{class:"border border-gray-400 p-1"},"PRODUCT DESCRIPTION"),t("td",{class:"border border-gray-400 p-1 text-center"},"DELIVERED QUANTITY"),t("td",{class:"border border-gray-400 p-1 text-center"},"RECEIVED QUANTITY"),t("td",{class:"border border-gray-400 p-1 text-center"},"VARIANCE"),t("td",{class:"border border-gray-400 p-1 text-right"},"UNIT COST"),t("td",{class:"border border-gray-400 p-1 text-right"},"TOTAL")]),t("tr",null,[t("td",{class:"border border-gray-400 p-1"},"TEST"),t("td",{class:"border border-gray-400 p-1 text-center"},"TEST"),t("td",{class:"border border-gray-400 p-1 text-center"},"TEST"),t("td",{class:"border border-gray-400 p-1"}),t("td",{class:"border border-gray-400 p-1 text-right"},"TEST"),t("td",{class:"border border-gray-400 p-1 text-right"},"TEST")]),t("tr",null,[t("td",{colspan:"5",class:"border border-gray-400 p-1 text-right font-bold"},"TOTAL"),t("td",{class:"border border-gray-400 p-1 text-right font-bold"},"TEST")]),t("tr",null,[t("td",{rowspan:"2",class:"border border-gray-400 p-1"},"PROMO"),t("td",{class:"border border-gray-400 p-1 text-center"},"DELIVERED QUANTITY"),t("td",{class:"border border-gray-400 p-1 text-center"},"RECEIVED QUANTITY"),t("td",{class:"border border-gray-400 p-1 text-center"},"VARIANCE"),t("td",{class:"border border-gray-400 p-1 text-right"},"UNIT COST"),t("td",{class:"border border-gray-400 p-1 text-right"},"AMOUNT")]),t("tr",null,[t("td",{class:"border border-gray-400 p-1 text-center"},"TEST"),t("td",{class:"border border-gray-400 p-1 text-center"},"TEST"),t("td",{class:"border border-gray-400 p-1"}),t("td",{class:"border border-gray-400 p-1 text-right"},"TEST"),t("td",{class:"border border-gray-400 p-1 text-right"},"TEST")]),t("tr",null,[t("td",{colspan:"5",class:"border border-gray-400 p-1 text-right font-bold"},"TOTAL"),t("td",{class:"border border-gray-400 p-1 text-right font-bold"},"TEST")]),t("tr",null,[t("td",{class:"border border-gray-400 p-1"},"PARTY CAKE OS NUMBER"),t("td",{colspan:"4",class:"border border-gray-400 p-1"},"DESIGN"),t("td",{class:"border border-gray-400 p-1 text-right"},"AMOUNT")]),t("tr",null,[t("td",{class:"border border-gray-400 p-1 font-bold"},"TEST"),t("td",{colspan:"4",class:"border border-gray-400 p-1"}),t("td",{class:"border border-gray-400 p-1 text-right font-bold"},"TEST")]),t("tr",null,[t("td",{colspan:"5",class:"border border-gray-400 p-1 font-bold"},"TOTAL AMOUNT"),t("td",{class:"border border-gray-400 p-1 text-right font-bold"},"TEST")]),t("tr",null,[t("td",{colspan:"3",class:"border border-gray-400 p-1"},[i(" ENDORSED BY:DISPATCHING"),t("br"),t("span",{class:"font-bold"},"________________________________"),t("br"),i(" BREADS/CAKES"),t("br"),i(" NAME & SIGNATURE/ DATE ")]),t("td",{colspan:"3",class:"border border-gray-400 p-1"},[t("span",{class:"font-bold"},"________________________________"),t("br"),i(" DELIVERED BY:LOGISTICS"),t("br"),i(" NAME & SIGNATURE/ DATE ")])]),t("tr",null,[t("td",{colspan:"6",class:"border border-gray-400 p-1 font-bold"},"CRATES QUANTITY DELIVERED")]),t("tr",null,[t("td",{colspan:"2",class:"border border-gray-400 p-1"},"ORANGE CRATES"),t("td",{colspan:"2",class:"border border-gray-400 p-1"},"BLUE CRATES"),t("td",{colspan:"2",class:"border border-gray-400 p-1"},"EMPANADA CRATES")])],-1)])))])])]),_:1})}}};export{Ue as default};
