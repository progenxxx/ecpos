import{D as M,Q as lt,d as i,T as at,s as V,p as nt,c as I,w as v,o as b,f as j,a as t,b as p,g as O,i as dt,j as F,x as it,u as _,e as f,h as k,t as c,F as R,n as ct,v as bt}from"./app-Do8mhcc_.js";import{_ as pt,a as ut}from"./Update-CGm56EGF.js";import{_ as C}from"./Modal.vue_vue_type_style_index_0_scoped_fd08cd24_lang-Cw8u42Sx.js";import{_ as gt}from"./TransparentButton-EuWNYQiU.js";import{S as mt}from"./SearchColored-BdPcGYYt.js";import{B as ft}from"./Back-DH6hWANX.js";import{F as ht}from"./File-CKbWh4Oz.js";import{_ as vt}from"./AdminPanel-B-hw9tyY.js";import"./Modal-BufSFMTr.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./InputError-Bl4xi_3e.js";import"./FormComponent-DbpXKqv4.js";import"./RetailGroup-D_6SfRWw.js";import"./Logout-MAsyIaFu.js";/* empty css                                                             */import"./RetailItems-SitUjnLk.js";import"./Attendance-Bi3B0c_5.js";const xt={class:"absolute adjust"},yt={class:"flex justify-start items-center"},Tt=["value"],wt={class:"mr-2"},Et={role:"tablist",class:"tabs tabs-lifted mt-10 p-5"},At={role:"tabpanel",class:"tab-content !bg-gray-200 !border-gray-300 p-6 h-[70vh] overflow-y-auto"},Ct={class:"container mx-auto px-4"},Dt={class:"flex flex-wrap -mx-4"},_t={key:0,class:"col-span-full text-center mt-8"},Lt={key:1,class:"col-span-full text-center mt-8"},St={class:"text-red-600 text-lg"},It={key:2,class:"col-span-full text-center mt-8"},Ot={class:"bg-blue-600 text-white text-center py-1 font-semibold"},kt={class:"bg-blue-400 text-white text-center py-1"},Rt={class:"w-full px-4 mb-8"},Nt={class:"flex bg-gray-200 font-semibold"},$t={class:"w-1/4 p-2 text-center border-r border-gray-400"},Ut={class:"divide-y divide-gray-300"},Pt={class:"w-1/2 p-2 border-r border-gray-300"},Mt={class:"w-1/4 p-2 text-center border-r border-gray-300"},Vt={class:"w-1/4 p-2 text-center"},jt=["onUpdate:modelValue","onInput","disabled","title"],Ft={class:"flex bg-red-200"},Ht={class:"w-1/4 p-2 text-center border-r border-gray-300"},Yt={class:"w-1/4 p-2 text-center"},Gt={class:"max-w-md mx-auto border border-gray-300"},zt={class:"w-full"},Jt={class:"border-b border-gray-300 p-2"},Bt={class:"border-b border-l border-gray-300 p-2 text-sm text-right"},Kt={class:"p-2"},qt={class:"border-l border-gray-300 p-2 text-sm text-right"},ge={__name:"cakes",setup(Qt){M.defaults.headers.common["X-CSRF-TOKEN"]=document.querySelector('meta[name="csrf-token"]').getAttribute("content");const L=lt(),H=i(L.props.rbostoretables),T=i(Object.entries(L.props.groupedPicklist).reduce((o,[e,l])=>(o[e]=l.map(r=>({...r,actual:r.ACTUAL})),o),{}));i(Object.entries(L.props.groupedPicklist).reduce((o,[e,l])=>(o[e]=l.map(r=>({...r,actual:r.ACTUAL})),o),{}));const u=o=>new Date(o).toLocaleDateString(),Y=i(""),G=i(""),z=i(""),N=i(!1),$=i(!1),J=i(!1),U=i(null),x=at({STORE:""}),B=()=>{x.get(route("cakepicklist.getstore"),{preserveScroll:!0})};i(null),V(()=>{const o=x.StartDate;if(o){const e=new Date(o),l=e.getFullYear(),r=String(e.getMonth()+1).padStart(2,"0"),a=String(e.getDate()).padStart(2,"0");return`${l}-${r}-${a}`}return""}),i(null),V(()=>{const o=x.EndDate;if(o){const e=new Date(o),l=e.getFullYear(),r=String(e.getMonth()+1).padStart(2,"0"),a=String(e.getDate()).padStart(2,"0");return`${l}-${r}-${a}`}return""});const K=()=>{N.value=!1},q=()=>{$.value=!1},Q=o=>o.reduce((e,l)=>e+parseFloat(l.COUNTED||0),0),D=(o,e)=>o.reduce((l,r)=>l+(r[e]||0),0),X=o=>o.reduce((e,l)=>e+parseFloat(l.actual||0),0),g=o=>{const e=parseFloat(o);return Number.isInteger(e)?e.toString():Math.round(e).toString()},W=async(o,e,l,r)=>{try{const d=T.value[o].find(h=>h.ITEMID===l);if(!d){console.error("Item not found");return}if(!d.JOURNALID){console.error("JOURNALID is missing for this item");return}const s=await M.post("/api/update-actual",{journal_id:d.JOURNALID,store_name:o,item_name:e,item_id:l,actual:r});s.data.success?d.ACTUAL=r:console.error("Failed to update ACTUAL value",s.data)}catch(a){a.response&&a.response.data?(console.error("Server validation errors:",a.response.data.errors),Object.entries(a.response.data.errors).forEach(([d,s])=>{console.error(`${d}: ${s.join(", ")}`)})):console.error("Error updating ACTUAL value:",a.message)}},Z=i(null),tt=()=>{window.location.href="/PickListInputData"},et=()=>{location.reload()},ot=()=>{window.location.href="/mgcount"},rt=()=>{const o=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),e=Object.entries(T.value);let l="";for(let r=0;r<e.length;r+=2){const a=e.slice(r,r+2).map(([d,s])=>{var y,n,m,E;const h=s.map(A=>`
        <tr>
          <td class="border p-1">${A.ITEMNAME}</td>
          <td class="border p-1 text-center">${g(A.COUNTED)}</td>
          <td class="border p-1 text-center"></td>
        </tr>
      `).join(""),w=D(s,"COUNTED");return D(s,"ACTUAL"),`
        <div class="store-section">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST - ${d}</div>
          <div class="bg-blue-400">DELIVERY DATE: ${u(s[0].DELIVERYDATE)}</div>
          <table>
            <thead>
              <tr>
                <th class="border p-1">PRODUCT</th>
                <th class="border p-1">${d}</th>
                <th class="border p-1">ACTUAL</th>
              </tr>
            </thead>
            <tbody>
              ${h}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${g(w)}</td>
                <td class="border p-1 text-center font-bold"></td>
              </tr>
            </tbody>
          </table>
          <table style="margin-top: 5px;">
            <tbody>
              <tr>
                <td class="text-red font-semibold" style="width: 20%;">DISPATCHER:</td>
                <td style="width: 60%;">
                  <div>${(y=s[0])==null?void 0:y.DISPATCHER}</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">${u((n=s[0])==null?void 0:n.DELIVERYDATE)}</td>
              </tr>
              <tr>
                <td class="font-semibold" style="width: 20%;">LOGISTICS:</td>
                <td style="width: 60%;">
                  <div>${(m=s[0])==null?void 0:m.LOGISTICS}</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">${u((E=s[0])==null?void 0:E.DELIVERYDATE)}</td>
              </tr>
            </tbody>
          </table>
        </div>
      `}).join("");l+=`
      <div class="page-container">
        ${a}
      </div>
    `}o.document.write(`
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
        ${l}
      </body>
    </html>
  `),o.document.close(),o.focus(),o.print(),o.close()},st=()=>{const o=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),e=Object.entries(T.value);let l="";for(let r=0;r<e.length;r+=2){const a=e.slice(r,r+2).map(([d,s])=>{var n,m,E,A,P;const h=s.map(S=>`
        <tr>
          <td class="border p-1">${S.ITEMNAME}</td>
          <td class="border p-1 text-center">${g(S.COUNTED)}</td>
          <td class="border p-1 text-center">${g(S.CHECKINGCOUNT)}</td>
        </tr>
      `).join(""),w=D(s,"COUNTED"),y=D(s,"ACTUAL");return`
        <div class="store-section">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST - ${d}</div>
          <div class="bg-blue-400">DELIVERY DATE: ${u((n=s[0])==null?void 0:n.DELIVERYDATE)}</div>
          <table>
            <thead>
              <tr>
                <th class="border p-1">PRODUCT</th>
                <th class="border p-1">${d}</th>
                <th class="border p-1">ACTUAL</th>
              </tr>
            </thead>
            <tbody>
              ${h}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${g(w)}</td>
                <td class="border p-1 text-center font-bold">${g(y)}</td>
              </tr>
            </tbody>
          </table>
          <table style="margin-top: 5px;">
            <tbody>
              <tr>
                <td class="text-red font-semibold" style="width: 20%;">DISPATCHER:</td>
                <td style="width: 60%;">
                  <div>${(m=s[0])==null?void 0:m.DISPATCHER}</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">${u((E=s[0])==null?void 0:E.DELIVERYDATE)}</td>
              </tr>
              <tr>
                <td class="font-semibold" style="width: 20%;">LOGISTICS:</td>
                <td style="width: 60%;">
                  <div>${(A=s[0])==null?void 0:A.LOGISTICS}</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">${u((P=s[0])==null?void 0:P.DELIVERYDATE)}</td>
              </tr>
            </tbody>
          </table>
        </div>
      `}).join("");l+=`
      <div class="page-container">
        ${a}
      </div>
    `}o.document.write(`
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
        ${l}
      </body>
    </html>
  `),o.document.close(),o.focus(),o.print(),o.close()};return(o,e)=>{const l=nt("InputLabel");return b(),I(vt,{"active-tab":"FGCOUNT"},{modals:v(()=>[$.value?(b(),I(pt,{key:0,onToggleActive:q})):j("",!0),N.value?(b(),I(ut,{key:1,ID:Y.value,SUBJECT:G.value,DESCRIPTION:z.value,onToggleActive:K},null,8,["ID","SUBJECT","DESCRIPTION"])):j("",!0)]),main:v(()=>[t("div",xt,[t("div",yt,[p(C,{type:"button",onClick:ot,class:"m-1 ml-2 bg-navy p-10 mt-4"},{default:v(()=>[p(ft,{class:"h-5"})]),_:1}),p(C,{type:"button",onClick:rt,class:"bg-navy tooltip tooltip-right tooltip-primary mt-4","data-tip":"Packing List Template"},{default:v(()=>[p(ht,{class:"h-5"})]),_:1}),p(C,{type:"button",onClick:st,class:"ml-1 mt-4 bg-navy"},{default:v(()=>e[1]||(e[1]=[O(" PRINT PL ")])),_:1}),p(C,{type:"button",onClick:tt,class:"ml-2 mt-4 bg-navy"},{default:v(()=>e[2]||(e[2]=[O(" GET ")])),_:1}),p(C,{type:"button",onClick:et,class:"ml-2 bg-navy mt-4"},{default:v(()=>e[3]||(e[3]=[O(" CALCULATE ")])),_:1}),e[5]||(e[5]=t("details",{className:"dropdown"},[t("summary",{className:"btn m-1 mt-4  bg-green-900 text-white hover:bg-navy"},"Select Route"),t("ul",{className:"menu dropdown-content !bg-gray-100 rounded-box z-[1] w-52 p-2 shadow"},[t("li",null,[t("a",{href:"/cakepicklist"},"ALL")]),t("li",null,[t("a",{href:"/plc-south1"},"SOUTH 1")]),t("li",null,[t("a",{href:"/plc-south2"},"SOUTH 2")]),t("li",null,[t("a",{href:"/plc-south3"},"SOUTH 3")]),t("li",null,[t("a",{href:"/plc-north1"},"NORTH 1")]),t("li",null,[t("a",{href:"/plc-north2"},"NORTH 2")]),t("li",null,[t("a",{href:"/plc-central"},"CENTRAL")]),t("li",null,[t("a",{href:"/plc-east"},"EAST")])])],-1)),t("form",{onSubmit:dt(B,["prevent"]),class:"flex items-center mt-4"},[t("input",{type:"hidden",name:"_token",value:o.$page.props.csrf_token},null,8,Tt),t("div",wt,[p(l,{for:"STORE",value:"STORE",class:"sr-only"}),F(t("select",{id:"STORE","onUpdate:modelValue":e[0]||(e[0]=r=>_(x).STORE=r),class:"input input-bordered w-64 !bg-gray-100"},[e[4]||(e[4]=t("option",{disabled:"",value:""},"Select Store",-1)),(b(!0),f(R,null,k(H.value,r=>(b(),f("option",{key:r.STOREID},c(r.NAME),1))),128))],512),[[it,_(x).STORE]])]),p(gt,{type:"submit",disabled:_(x).processing,class:ct({"opacity-25":_(x).processing})},{default:v(()=>[p(mt,{class:"h-8"})]),_:1},8,["disabled","class"])],32)])]),t("div",Et,[e[17]||(e[17]=t("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab !bg-gray-200 !border-gray-300 !font-bold","aria-label":"PICK LIST",checked:""},null,-1)),t("div",At,[t("div",Ct,[t("div",Dt,[J.value?(b(),f("div",_t,e[6]||(e[6]=[t("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):U.value?(b(),f("div",Lt,[t("p",St,c(U.value),1)])):!T.value||Object.keys(T.value).length===0?(b(),f("div",It,e[7]||(e[7]=[t("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[t("p",{class:"text-gray-600 text-base sm:text-lg"},"No Pick List Available")],-1)]))):(b(!0),f(R,{key:3},k(T.value,(r,a)=>{var d,s,h,w,y;return b(),f("div",{key:a,class:"w-full mb-8"},[t("div",{class:"max-w-xl mx-auto bg-white shadow-lg",ref_for:!0,ref_key:"printableContent",ref:Z},[e[15]||(e[15]=t("div",{class:"bg-blue-800 text-white text-center py-2 font-bold"}," ELJIN CORPORATION ",-1)),t("div",Ot," PACKING LIST - "+c(a),1),t("div",kt," DELIVERY DATE: "+c(u((d=r[0])==null?void 0:d.DELIVERYDATE)||"Not available"),1),t("div",Rt,[t("div",Nt,[e[8]||(e[8]=t("div",{class:"w-1/2 p-2 border-r border-gray-400"},"PRODUCT",-1)),t("div",$t,c(a),1),e[9]||(e[9]=t("div",{class:"w-1/4 p-2 text-center"},"ACTUAL",-1))]),t("div",Ut,[(b(!0),f(R,null,k(r,n=>(b(),f("div",{key:n.ITEMID,class:"flex"},[t("div",Pt,c(n.ITEMNAME),1),t("div",Mt,c(g(n.COUNTED)),1),t("div",Vt,[F(t("input",{"onUpdate:modelValue":m=>n.ACTUAL=m,type:"number",class:"w-full text-center border border-gray-300 rounded",onInput:m=>W(a,n.ITEMNAME,n.ITEMID,m.target.value),disabled:!n.JOURNALID||!n.ITEMID,title:!n.JOURNALID||!n.ITEMID?"Cannot update: Missing required data":""},null,40,jt),[[bt,n.ACTUAL]])])]))),128)),t("div",Ft,[e[10]||(e[10]=t("div",{class:"w-1/2 p-2 border-r border-gray-300"},"TOTAL",-1)),t("div",Ht,c(g(Q(r))),1),t("div",Yt,c(g(X(r))),1)])])]),t("div",Gt,[t("table",zt,[t("tr",null,[e[12]||(e[12]=t("td",{class:"border-b border-r border-gray-300 p-2 text-red-600 font-semibold"},"DISPATCHER:",-1)),t("td",Jt,[t("div",null,c(((s=r[0])==null?void 0:s.DISPATCHER)||"Not available"),1),e[11]||(e[11]=t("div",{class:"border-b border-gray-300 mt-4"},null,-1))]),t("td",Bt,c(u((h=r[0])==null?void 0:h.DELIVERYDATE)||"Not available"),1)]),t("tr",null,[e[14]||(e[14]=t("td",{class:"border-r border-gray-300 p-2 font-semibold"},"LOGISTICS:",-1)),t("td",Kt,[t("div",null,c(((w=r[0])==null?void 0:w.LOGISTICS)||"Not available"),1),e[13]||(e[13]=t("div",{class:"border-b border-gray-300 mt-4"},null,-1))]),t("td",qt,c(u((y=r[0])==null?void 0:y.DELIVERYDATE)||"Not available"),1)])])]),e[16]||(e[16]=t("br",null,null,-1))],512)])}),128))])])])])]),_:1})}}};export{ge as default};
