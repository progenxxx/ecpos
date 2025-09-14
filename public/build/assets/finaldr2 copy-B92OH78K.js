import{C as P,Q as $,d as k,q as T,T as H,p as G,c as D,w as A,o as a,f as I,a as t,b as O,g as n,i as Y,j as B,s as _,u as g,e as c,h as f,t as l,F as C,n as F}from"./app-BoaMMdkU.js";import{_ as j,a as K}from"./Update-q09uPh_I.js";import{_ as Q}from"./Modal.vue_vue_type_style_index_0_scoped_fd08cd24_lang-DFF7VX6Y.js";import{_ as z}from"./TransparentButton-CpYv8pl2.js";import{S as q}from"./SearchColored-CCLDVocz.js";import{_ as J}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{_ as W}from"./AdminPanel-CRYPwC5J.js";import"./Modal-5-iMnX7J.js";import"./InputError-DS750Wq9.js";import"./FormComponent-K8EX_DC3.js";import"./RetailGroup-BjeigQ0x.js";import"./Logout-BJuIbz2k.js";/* empty css                                                             */import"./RetailItems-BB1M731N.js";import"./Attendance-CU9DKH9f.js";const X={class:"flex justify-start mb-4"},Z=["value"],tt={class:"ml-2"},rt={class:"ml-2"},et={role:"tablist",class:"tabs tabs-lifted mt-4 p-5"},st={role:"tabpanel",class:"tab-content bg-base-100 border-base-200 p-6 h-[85vh] overflow-y-auto"},ot={key:0,class:"col-span-full text-center mt-8"},lt={key:1,class:"col-span-full text-center mt-8"},dt={class:"text-xl font-bold mb-4"},at={class:"max-w-4xl mx-auto p-4 bg-white shadow-lg text-xs"},nt={class:"w-full border-collapse border border-gray-400"},bt={colspan:"2",class:"border border-gray-400 p-1"},pt={class:"font-bold"},it={colspan:"3",class:"border border-gray-400 p-1"},ct={class:"font-bold"},ut={class:"border border-gray-400 p-1"},Et={class:"border border-gray-400 p-1 text-center"},Tt={class:"border border-gray-400 p-1 text-center"},gt={class:"border border-gray-400 p-1 text-right"},Ct={class:"border border-gray-400 p-1 text-right"},At={class:"border border-gray-400 p-1 text-right font-bold"},Ot={class:"border border-gray-400 p-1 text-center"},ft={class:"border border-gray-400 p-1 text-center"},St={class:"border border-gray-400 p-1 text-right"},Dt={class:"border border-gray-400 p-1 text-right"},It={class:"border border-gray-400 p-1 text-right font-bold"},yt={colspan:"3",class:"border border-gray-400 p-1"},Rt={class:"font-bold"},Nt={colspan:"3",class:"border border-gray-400 p-1"},mt={class:"font-bold"},xt={__name:"finaldr2 copy",props:{sptrans:{type:Array,required:!0},rbostoretables:{type:Array,required:!0}},setup(y){P.defaults.headers.common["X-CSRF-TOKEN"]=document.querySelector('meta[name="csrf-token"]').getAttribute("content");const v=$(),b=k(Object.entries(v.props.groupedPicklist).reduce((e,[r,d])=>(e[r]=d.map(s=>({...s,actual:s.ACTUAL})),e),{})),u=y,L=T(()=>Object.keys(b.value));T(()=>{const e=Object.values(b.value)[0];return e||[]});const R=e=>new Date(e).toLocaleDateString(),i=e=>new Intl.NumberFormat("en-PH",{style:"currency",currency:"PHP"}).format(e),N=e=>e.reduce((r,d)=>r+Number(d.CHECKINGCOUNT||0)*Number(d.COST||0),0),U=e=>e.reduce((r,d)=>r+Number(d.COUNTED||0)*Number(d.COST||0),0),E=H({StartDate:"2024-07-22",StoreName:""}),h=()=>{E.get(route("picklist.getrange"),{preserveState:!0,preserveScroll:!0})};T(()=>{const e=E.StartDate;if(e){const r=new Date(e),d=r.getFullYear(),s=String(r.getMonth()+1).padStart(2,"0"),o=String(r.getDate()).padStart(2,"0");return`${d}-${s}-${o}`}return""}),T(()=>Object.values(b.value).flat().reduce((e,r)=>e+Number(r.COST||0)*Number(r.CHECKINGCOUNT||0),0));const m=T(()=>Object.values(b.value).flat().find(e=>Number(e.SPECIALORDER||0)>0)||{});T(()=>Number(m.value.COST||0)*Number(m.value.SPECIALORDER||0));const w=e=>{const r=parseFloat(e);return Number.isInteger(r)?r.toString():Math.round(r).toString()},M=()=>{const e=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),r=Object.entries(b.value);let d="";r.forEach(([s,o])=>{const S=o.map(p=>`
      <tr>
        <td class="border p-1">${p.ITEMNAME}</td>
        <td class="border p-1 text-center">${w(p.CHECKINGCOUNT)}</td>
        <td class="border p-1 text-center">0</td>
        <td class="border p-1 text-center">${0-p.CHECKINGCOUNT}</td>
        <td class="border p-1 text-right">${i(p.COST)}</td>
        <td class="border p-1 text-right">${i(p.COST*p.CHECKINGCOUNT)}</td>
      </tr>
    `).join(""),V=N(o);let x="";s==="SOUTH 1"&&u.sptrans&&u.sptrans.length>0&&(x=`
        <tr>
          <td colspan="6" class="border p-1 font-bold">SPECIAL ORDERS</td>
        </tr>
        <tr>
          <td class="border p-1">PROMO</td>
          <td class="border p-1 text-center">DELIVERED QUANTITY</td>
          <td class="border p-1 text-center">RECEIVED QUANTITY</td>
          <td class="border p-1 text-center">VARIANCE</td>
          <td class="border p-1 text-right">UNIT COST</td>
          <td class="border p-1 text-right">AMOUNT</td>
        </tr>
        ${u.sptrans.map(p=>`
          <tr>
            <td class="border p-1 text-center">${p.ITEMNAME}</td>
            <td class="border p-1 text-center">${p.COUNTED}</td>
            <td class="border p-1 text-center">0</td>
            <td class="border p-1 text-center">0</td> 
            <td class="border p-1 text-right">${i(p.COST)}</td>
            <td class="border p-1 text-right">${i(p.COST*p.COUNTED)}</td>
          </tr>
        `).join("")}
      `),d+=`
      <div class="page-container">
        <div class="store-section">
          <table>
            <tr>
              <td colspan="6" class="text-center font-bold text-lg border p-1">
                MALIWALO<br>
                TARLAC CITY
              </td>
            </tr>
            <tr>
              <td colspan="4" class="font-bold border p-1">DELIVERY GOODS RECEIPT<br>BW PRODUCT</td>
              <td colspan="2" class="border p-1">
                DR #: <span class="font-bold">${o[0].JOURNALID}</span><br>
                DELIVERY DATE: ${R(o[0].DELIVERYDATE)}
              </td>
            </tr>
            <tr>
              <td colspan="3" class="border p-1">RECEIVED FROM:<br><span class="font-bold">HEADOFFICE</span></td>
              <td colspan="3" class="border p-1">DELIVERED TO:<br><span class="font-bold">${s}</span></td>
            </tr>
            <tr class="bg-gray-200 font-bold">
              <td class="border p-1">PRODUCT DESCRIPTION</td>
              <td class="border p-1 text-center">DELIVERED QUANTITY</td>
              <td class="border p-1 text-center">RECEIVED QUANTITY</td>
              <td class="border p-1 text-center">VARIANCE</td>
              <td class="border p-1 text-right">UNIT COST</td>
              <td class="border p-1 text-right">TOTAL</td>
            </tr>
            ${S}
            ${x}
            <tr>
              <td colspan="5" class="border p-1 text-right font-bold">TOTAL</td>
              <td class="border p-1 text-right font-bold">${i(V)}</td>
            </tr>
            <tr>
              <td colspan="3" class="border p-1">
                ENDORSED BY:DISPATCHING<br>
                <span class="font-bold">${o[0].DISPATCHER}</span><br>
                BREADS/CAKES<br>
                NAME & SIGNATURE/ DATE
              </td>
              <td colspan="3" class="border p-1">
                <span class="font-bold">${o[0].LOGISTICS}</span><br>
                DELIVERED BY:LOGISTICS<br>
                NAME & SIGNATURE/ DATE
              </td>
            </tr>
            <tr>
              <td colspan="6" class="border p-1 font-bold">CRATES QUANTITY DELIVERED</td>
            </tr>
            <tr>
              <td colspan="2" class="border p-1">ORANGE CRATES</td>
              <td colspan="2" class="border p-1">BLUE CRATES</td>
              <td colspan="2" class="border p-1">EMPANADA CRATES</td>
            </tr>
          </table>
        </div>
      </div>
    `}),e.document.write(`
    <html>
      <head>
        <title>Delivery Goods Receipt</title>
        <style>
          @page {
            size: legal portrait;
            margin: 0;
          }
          body { 
            font-family: Arial, sans-serif; 
            font-size: 12px;
            margin: 0;
            padding: 0;
          }
          .page-container {
            width: 100%;
            height: 100vh;
            page-break-after: always;
            padding: 10mm;
            box-sizing: border-box;
          }
          .store-section { 
            width: 100%; 
          }
          table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5mm;
          }
          th, td { 
            border: 1px solid black; 
            padding: 2px; 
            font-size: 12px; 
          }
          .text-center { text-align: center; }
          .text-right { text-align: right; }
          .font-bold { font-weight: bold; }
          .bg-gray-200 { background-color: #edf2f7; }
        </style>
      </head>
      <body>
        ${d}
      </body>
    </html>
  `),e.document.close(),e.focus(),e.print(),e.close()};return(e,r)=>{const d=G("InputLabel");return a(),D(W,{"active-tab":"FINALDR"},{modals:A(()=>[e.showCreateModal?(a(),D(j,{key:0,onToggleActive:e.createModalHandler},null,8,["onToggleActive"])):I("",!0),e.showModalUpdate?(a(),D(K,{key:1,ID:e.id,SUBJECT:e.subject,DESCRIPTION:e.description,onToggleActive:e.updateModalHandler},null,8,["ID","SUBJECT","DESCRIPTION","onToggleActive"])):I("",!0)]),main:A(()=>[t("div",X,[O(Q,{type:"button",onClick:M,class:"bg-navy text-white ml-4 mt-4 rounded-md text-sm"},{default:A(()=>r[1]||(r[1]=[n(" PRINT ")])),_:1}),t("form",{onSubmit:Y(h,["prevent"]),class:"flex items-center mt-4"},[t("input",{type:"hidden",name:"_token",value:e.$page.props.csrf_token},null,8,Z),t("div",tt,[O(d,{for:"STORE",value:"STORE",class:"sr-only"}),B(t("select",{id:"STORE","onUpdate:modelValue":r[0]||(r[0]=s=>g(E).STORE=s),class:"input input-bordered w-64"},[r[2]||(r[2]=t("option",{disabled:"",value:""},"Select Store",-1)),(a(!0),c(C,null,f(y.rbostoretables,s=>(a(),c("option",{key:s.STOREID},l(s.NAME),1))),128))],512),[[_,g(E).STORE]])]),O(z,{type:"submit",disabled:g(E).processing,class:F({"opacity-25":g(E).processing})},{default:A(()=>[O(q,{class:"h-8"})]),_:1},8,["disabled","class"])],32),r[3]||(r[3]=t("details",{className:"dropdown mt-2"},[t("summary",{className:"btn m-1"},"Select Route"),t("ul",{className:"menu dropdown-content bg-base-100 rounded-box z-[1] w-52 p-2 shadow"},[t("li",null,[t("a",{href:"/mgcount"},"ALL")]),t("li",null,[t("a",{href:"/south1"},"SOUTH 1")]),t("li",null,[t("a",{href:"/south2"},"SOUTH 2")]),t("li",null,[t("a",{href:"/south3"},"SOUTH 3")]),t("li",null,[t("a",{href:"/north1"},"NORTH 1")]),t("li",null,[t("a",{href:"/north2"},"NORTH 2")]),t("li",null,[t("a",{href:"/central"},"CENTRAL")]),t("li",null,[t("a",{href:"/east"},"EAST")])])],-1)),t("h6",rt,l(e.routes),1)]),t("div",et,[r[33]||(r[33]=t("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab","aria-label":"FINAL DR",checked:""},null,-1)),t("div",st,[g(E).processing?(a(),c("div",ot,r[4]||(r[4]=[t("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):!b.value||Object.keys(b.value).length===0?(a(),c("div",lt,r[5]||(r[5]=[t("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[t("p",{class:"text-gray-600 text-base sm:text-lg"},"No DR2 List Available")],-1)]))):(a(!0),c(C,{key:2},f(L.value,s=>(a(),c("div",{key:s,class:"mb-8"},[t("h2",dt,l(s),1),t("div",at,[t("table",nt,[r[29]||(r[29]=t("tr",null,[t("td",{colspan:"6",class:"text-center font-bold text-lg border border-gray-400 p-1"},[n(" MALIWALO"),t("br"),n(" TARLAC CITY ")])],-1)),t("tr",null,[r[8]||(r[8]=t("td",{colspan:"4",class:"font-bold border border-gray-400 p-1"},[n("DELIVERY GOODS RECEIPT"),t("br"),n("BW PRODUCT")],-1)),t("td",bt,[r[6]||(r[6]=n(" DR #: ")),t("span",pt,l(b.value[s][0].JOURNALID),1),r[7]||(r[7]=t("br",null,null,-1)),n(" DELIVERY DATE: "+l(R(b.value[s][0].DELIVERYDATE)),1)])]),t("tr",null,[r[11]||(r[11]=t("td",{colspan:"3",class:"border border-gray-400 p-1"},[n("RECEIVED FROM:"),t("br"),t("span",{class:"font-bold"},"HEADOFFICE")],-1)),t("td",it,[r[9]||(r[9]=n("DELIVERED TO:")),r[10]||(r[10]=t("br",null,null,-1)),t("span",ct,l(s),1)])]),r[30]||(r[30]=t("tr",{class:"bg-gray-200 font-bold"},[t("td",{class:"border border-gray-400 p-1"},"PRODUCT DESCRIPTION"),t("td",{class:"border border-gray-400 p-1 text-center"},"DELIVERED QUANTITY"),t("td",{class:"border border-gray-400 p-1 text-center"},"RECEIVED QUANTITY"),t("td",{class:"border border-gray-400 p-1 text-center"},"VARIANCE"),t("td",{class:"border border-gray-400 p-1 text-right"},"UNIT COST"),t("td",{class:"border border-gray-400 p-1 text-right"},"TOTAL")],-1)),(a(!0),c(C,null,f(b.value[s],o=>(a(),c("tr",{key:o.ITEMID},[t("td",ut,l(o.ITEMNAME),1),t("td",Et,l(o.CHECKINGCOUNT),1),r[12]||(r[12]=t("td",{class:"border border-gray-400 p-1 text-center"},"0",-1)),t("td",Tt,l(o.actual-o.CHECKINGCOUNT),1),t("td",gt,l(i(o.COST)),1),t("td",Ct,l(i(o.COST*o.CHECKINGCOUNT)),1)]))),128)),t("tr",null,[r[13]||(r[13]=t("td",{colspan:"5",class:"border border-gray-400 p-1 text-right font-bold"},"TOTAL",-1)),t("td",At,l(i(N(b.value[s]))),1)]),s==="SOUTH 1"&&u.sptrans&&u.sptrans.length>0?(a(),c(C,{key:0},[r[17]||(r[17]=t("tr",null,[t("td",{colspan:"6",class:"border border-gray-400 p-1 font-bold"},"SPECIAL ORDERS")],-1)),r[18]||(r[18]=t("tr",null,[t("td",{class:"border border-gray-400 p-1"},"PROMO"),t("td",{class:"border border-gray-400 p-1 text-center"},"DELIVERED QUANTITY"),t("td",{class:"border border-gray-400 p-1 text-center"},"RECEIVED QUANTITY"),t("td",{class:"border border-gray-400 p-1 text-center"},"VARIANCE"),t("td",{class:"border border-gray-400 p-1 text-right"},"UNIT COST"),t("td",{class:"border border-gray-400 p-1 text-right"},"AMOUNT")],-1)),(a(!0),c(C,null,f(u.sptrans,(o,S)=>(a(),c("tr",{key:S},[t("td",Ot,l(o.ITEMNAME),1),t("td",ft,l(o.COUNTED),1),r[14]||(r[14]=t("td",{class:"border border-gray-400 p-1 text-center"},"0",-1)),r[15]||(r[15]=t("td",{class:"border border-gray-400 p-1 text-center"},"0",-1)),t("td",St,l(i(o.COST)),1),t("td",Dt,l(i(o.COST*o.COUNTED)),1)]))),128)),t("tr",null,[r[16]||(r[16]=t("td",{colspan:"5",class:"border border-gray-400 p-1 text-right font-bold"},"TOTAL",-1)),t("td",It,l(i(U(u.sptrans))),1)])],64)):I("",!0),t("tr",null,[t("td",yt,[r[19]||(r[19]=n(" ENDORSED BY:DISPATCHING")),r[20]||(r[20]=t("br",null,null,-1)),t("span",Rt,l(b.value[s][0].DISPATCHER),1),r[21]||(r[21]=t("br",null,null,-1)),r[22]||(r[22]=n(" BREADS/CAKES")),r[23]||(r[23]=t("br",null,null,-1)),r[24]||(r[24]=n(" NAME & SIGNATURE/ DATE "))]),t("td",Nt,[t("span",mt,l(b.value[s][0].LOGISTICS),1),r[25]||(r[25]=t("br",null,null,-1)),r[26]||(r[26]=n(" DELIVERED BY:LOGISTICS")),r[27]||(r[27]=t("br",null,null,-1)),r[28]||(r[28]=n(" NAME & SIGNATURE/ DATE "))])]),r[31]||(r[31]=t("tr",null,[t("td",{colspan:"6",class:"border border-gray-400 p-1 font-bold"},"CRATES QUANTITY DELIVERED")],-1)),r[32]||(r[32]=t("tr",null,[t("td",{colspan:"2",class:"border border-gray-400 p-1"},"ORANGE CRATES"),t("td",{colspan:"2",class:"border border-gray-400 p-1"},"BLUE CRATES"),t("td",{colspan:"2",class:"border border-gray-400 p-1"},"EMPANADA CRATES")],-1))])])]))),128))])])]),_:1})}}},Ft=J(xt,[["__scopeId","data-v-33500b1f"]]);export{Ft as default};
