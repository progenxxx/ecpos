import{D as V,Q as F,d as $,s as y,T as K,p as k,c as N,w as x,o as b,f as h,a as t,b as D,g as n,i as Q,j as A,x as z,u as T,e as c,h as R,t as l,F as m,v as O,n as J}from"./app-CWbsghfQ.js";import{_ as q,a as W}from"./Update-B081ani1.js";import{_ as M}from"./Modal.vue_vue_type_style_index_0_scoped_fd08cd24_lang-Bmfe4oq8.js";import{_ as X}from"./TransparentButton-dnCRvYzW.js";import{S as Z}from"./SearchColored-DhBHhzRI.js";import{_ as tt}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{_ as rt}from"./AdminPanel-CYInrTuX.js";import"./Modal-DTsuLBrX.js";import"./InputError-CkL0frng.js";import"./FormComponent-BHLP4As8.js";import"./RetailGroup-BW1tkHLf.js";import"./Logout-BoWuqpqH.js";/* empty css                                                             */import"./RetailItems-CtMdT1Ax.js";import"./Attendance-D6oEmBx9.js";const et={class:"flex justify-start mb-4"},st={class:"rounded-md shadow-lg bg-blue-100 border-blue-900 ml-2 mr-2 pb-2"},ot=["value"],lt={class:"ml-2"},at={class:"relative ml-2"},dt=["placeholder"],nt={class:"ml-2 mt-6 font-bold"},bt={role:"tablist",class:"tabs tabs-lifted mt-4 p-5"},ut={role:"tabpanel",class:"tab-content !bg-gray-100 !border-gray-200 p-6 h-[85vh] overflow-y-auto"},pt={key:0,class:"col-span-full text-center mt-8"},it={key:1,class:"col-span-full text-center mt-8"},ct={class:"text-xl font-bold mb-4"},gt={class:"max-w-4xl mx-auto p-4 bg-white shadow-lg text-xs"},Et={class:"w-full border-collapse border border-gray-400"},Tt={colspan:"2",class:"border border-gray-400 p-1"},Ct={class:"font-bold"},ft={colspan:"3",class:"border border-gray-400 p-1"},yt={class:"font-bold"},Dt={class:"border border-gray-400 p-1"},At={class:"border border-gray-400 p-1 text-center"},xt={class:"border border-gray-400 p-1 text-center"},mt={class:"border border-gray-400 p-1 text-right"},Ot={class:"border border-gray-400 p-1 text-right"},vt={class:"border border-gray-400 p-1 text-right font-bold"},Rt={class:"border border-gray-400 p-1 text-center"},It={class:"border border-gray-400 p-1 text-center"},St={class:"border border-gray-400 p-1 text-right"},Nt={class:"border border-gray-400 p-1 text-right"},ht={class:"border border-gray-400 p-1 text-right font-bold"},Lt={colspan:"3",class:"border border-gray-400 p-1"},Ut={class:"font-bold"},wt={colspan:"3",class:"border border-gray-400 p-1"},Vt={class:"font-bold"},$t={colspan:"1",class:"border border-gray-400 p-2"},kt={class:"flex justify-between items-center"},Mt={class:"mr-2"},Pt=["onChange"],_t={colspan:"1",class:"border border-gray-400 p-2"},Yt={class:"flex justify-between items-center"},Ht={class:"mr-2"},Gt=["onChange"],Bt={colspan:"2",class:"border border-gray-400 p-2"},jt={class:"flex justify-between items-center"},Ft={class:"mr-2"},Kt=["onChange"],Qt={colspan:"2",class:"border border-gray-400 p-2"},zt={class:"flex justify-between items-center"},Jt={class:"mr-2"},qt=["onChange"],Wt={__name:"finaldr",props:{sptrans:{type:Array,required:!0},rbostoretables:{type:Array,required:!0},routes:{type:String,required:!0},getstore:{type:String,required:!0}},setup(I){V.defaults.headers.common["X-CSRF-TOKEN"]=document.querySelector('meta[name="csrf-token"]').getAttribute("content");const P=F(),a=$(Object.entries(P.props.groupedPicklist).reduce((s,[r,d])=>(s[r]=d.map(E=>({...E,actual:E.ACTUAL})),s),{})),C=I,_=y(()=>Object.keys(a.value));y(()=>{const s=Object.values(a.value)[0];return s||[]});const f=s=>new Date(s).toLocaleDateString(),i=s=>new Intl.NumberFormat("en-PH",{style:"currency",currency:"PHP"}).format(s),L=s=>s.reduce((r,d)=>r+Number(d.CHECKINGCOUNT||0)*Number(d.COST||0),0),Y=s=>s.reduce((r,d)=>r+Number(d.COUNTED||0)*Number(d.COST||0),0),g=K({EndDate:"",STORE:""}),H=()=>{g.get(route("fdr.getrange"),{preserveState:!0,preserveScroll:!0})};y(()=>{const s=g.StartDate;if(s){const r=new Date(s),d=r.getFullYear(),E=String(r.getMonth()+1).padStart(2,"0"),e=String(r.getDate()).padStart(2,"0");return`${d}-${E}-${e}`}return""}),y(()=>Object.values(a.value).flat().reduce((s,r)=>s+Number(r.COST||0)*Number(r.CHECKINGCOUNT||0),0));const U=y(()=>Object.values(a.value).flat().find(s=>Number(s.SPECIALORDER||0)>0)||{});y(()=>Number(U.value.COST||0)*Number(U.value.SPECIALORDER||0));const G=s=>{const r=parseFloat(s);return Number.isInteger(r)?r.toString():Math.round(r).toString()},u=$({orangeCrates:0,blueCrates:0,empanadaCrates:0,box:0}),v=async(s,r)=>{try{const d=await V.post("/api/update-crates-counts",{journalId:r,orangeCrates:u.value.orangeCrates,blueCrates:u.value.blueCrates,empanadaCrates:u.value.empanadaCrates,box:u.value.box});d.data.success?console.log("Crates counts updated successfully"):console.error("Failed to update crates counts",d.data)}catch(d){console.error("Error updating crates counts:",d.message)}},B=()=>{location.reload()},j=()=>{const s=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),r=Object.entries(a.value);let d="";r.forEach(([E,e])=>{const o=e.map(p=>`
      <tr>
        <td class="border p-1">${p.ITEMNAME}</td>
        <td class="border p-1 text-center">${G(p.CHECKINGCOUNT)}</td>
        <td class="border p-1 text-center">0</td>
        <td class="border p-1 text-center">${0-p.CHECKINGCOUNT}</td>
        <td class="border p-1 text-right">${i(p.COST)}</td>
        <td class="border p-1 text-right">${i(p.COST*p.CHECKINGCOUNT)}</td>
      </tr>
    `).join(""),S=L(e);let w="";E===""&&C.sptrans&&C.sptrans.length>0&&(w=`
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
        ${C.sptrans.map(p=>`
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
                DR #: <span class="font-bold">${e[0].JOURNALID}</span><br>
                DELIVERY DATE: ${f(e[0].DELIVERYDATE)}
              </td>
            </tr>
            <tr>
              <td colspan="3" class="border p-1">RECEIVED FROM:<br><span class="font-bold">HEADOFFICE</span></td>
              <td colspan="3" class="border p-1">DELIVERED TO:<br><span class="font-bold">${E}</span></td>
            </tr>
            <tr class="bg-gray-200 font-bold">
              <td class="border p-1">PRODUCT DESCRIPTION</td>
              <td class="border p-1 text-center">DELIVERED QUANTITY</td>
              <td class="border p-1 text-center">RECEIVED QUANTITY</td>
              <td class="border p-1 text-center">VARIANCE</td>
              <td class="border p-1 text-right">UNIT COST</td>
              <td class="border p-1 text-right">TOTAL</td>
            </tr>
            ${o}
            ${w}
            <tr>
              <td colspan="5" class="border p-1 text-right font-bold">TOTAL</td>
              <td class="border p-1 text-right font-bold">${i(S)}</td>
            </tr>
            <tr>
              <td colspan="3" class="border p-1">
                ENDORSED BY:DISPATCHING<br>
                <span class="font-bold">${e[0].DISPATCHER} | ${f(e[0].DELIVERYDATE)}</span><br>
                BREADS/CAKES<br>
                NAME & SIGNATURE/ DATE
              </td>
              <td colspan="3" class="border p-1">
                <span class="font-bold">${e[0].LOGISTICS} | ${f(e[0].DELIVERYDATE)}</span><br>
                DELIVERED BY:LOGISTICS<br>
                NAME & SIGNATURE/ DATE
              </td>
            </tr>
            <tr>
              <td colspan="6" class="border p-1 font-bold">CRATES QUANTITY DELIVERED</td>
            </tr>
            <tr>
              <td colspan="1" class="border p-1">ORANGE CRATES - ${e[0].orangeCrates}</td>
              <td colspan="1" class="border p-1">BLUE CRATES - ${e[0].blueCrates}</td>
              <td colspan="2" class="border p-1">EMPANADA CRATES - ${e[0].empanadaCrates}</td>
              <td colspan="3" class="border p-1">BOX - ${e[0].box}</td>
            </tr>
          </table>
        </div>
      </div>
    `}),s.document.write(`
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
  `),s.document.close(),s.focus(),s.print(),s.close()};return(s,r)=>{const d=k("InputLabel"),E=k("InputError");return b(),N(rt,{"active-tab":"FINALDR"},{modals:x(()=>[s.showCreateModal?(b(),N(q,{key:0,onToggleActive:s.createModalHandler},null,8,["onToggleActive"])):h("",!0),s.showModalUpdate?(b(),N(W,{key:1,ID:s.id,SUBJECT:s.subject,DESCRIPTION:s.description,onToggleActive:s.updateModalHandler},null,8,["ID","SUBJECT","DESCRIPTION","onToggleActive"])):h("",!0)]),main:x(()=>[t("div",et,[D(M,{type:"button",onClick:j,class:"bg-navy text-white ml-4 mt-4 rounded-md text-sm"},{default:x(()=>r[7]||(r[7]=[n(" PRINT ")])),_:1}),D(M,{type:"button",onClick:B,class:"ml-2 bg-navy mt-4"},{default:x(()=>r[8]||(r[8]=[n(" RELOAD ")])),_:1}),t("div",st,[t("form",{onSubmit:Q(H,["prevent"]),class:"flex items-center mt-4"},[t("input",{type:"hidden",name:"_token",value:s.$page.props.csrf_token},null,8,ot),t("div",lt,[D(d,{for:"STORE",value:"STORE",class:"sr-only"}),A(t("select",{id:"STORE","onUpdate:modelValue":r[0]||(r[0]=e=>T(g).STORE=e),class:"input input-bordered w-64 !bg-gray-100"},[r[9]||(r[9]=t("option",{disabled:"",value:""},"Select Store",-1)),(b(!0),c(m,null,R(I.rbostoretables,e=>(b(),c("option",{key:e.STOREID},l(e.NAME),1))),128))],512),[[z,T(g).STORE]])]),t("div",at,[r[10]||(r[10]=t("div",{class:"flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none"},[t("svg",{class:"w-5 h-5 text-gray-500 dark:text-gray-400",fill:"currentColor",viewBox:"0 0 20 20",xmlns:"http://www.w3.org/2000/svg"},[t("path",{"fill-rule":"evenodd",d:"M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z","clip-rule":"evenodd"})])],-1)),A(t("input",{id:"EndDate",type:"date","onUpdate:modelValue":r[1]||(r[1]=e=>T(g).EndDate=e),onInput:r[2]||(r[2]=(...e)=>s.formattedDate2&&s.formattedDate2(...e)),placeholder:s.formattedDate2,class:"bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500",required:""},null,40,dt),[[O,T(g).EndDate]]),D(E,{message:T(g).errors.EndDate,class:"mt-2"},null,8,["message"])]),D(X,{type:"submit",disabled:T(g).processing,class:J({"opacity-25":T(g).processing})},{default:x(()=>[D(Z,{class:"h-8"})]),_:1},8,["disabled","class"])],32)]),r[11]||(r[11]=t("details",{className:"dropdown mt-2"},[t("summary",{className:"btn m-1 !bg-navy !text-white"},"Select Route"),t("ul",{className:"menu dropdown-content !bg-gray-100 rounded-box z-[1] w-52 p-2 shadow"},[t("li",null,[t("a",{href:"/finalDR"},"ALL")]),t("li",null,[t("a",{href:"/fdr-south1"},"SOUTH 1")]),t("li",null,[t("a",{href:"/fdr-south2"},"SOUTH 2")]),t("li",null,[t("a",{href:"/fdr-south3"},"SOUTH 3")]),t("li",null,[t("a",{href:"/fdr-north1"},"NORTH 1")]),t("li",null,[t("a",{href:"/fdr-north2"},"NORTH 2")]),t("li",null,[t("a",{href:"/fdr-central"},"CENTRAL")]),t("li",null,[t("a",{href:"/fdr-east"},"EAST")])])],-1)),t("h6",nt,l(I.routes),1)]),t("div",bt,[r[44]||(r[44]=t("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab !bg-gray-100 !text-gray-500 !font-bold","aria-label":"FINAL DR",checked:""},null,-1)),t("div",ut,[T(g).processing?(b(),c("div",pt,r[12]||(r[12]=[t("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):!a.value||Object.keys(a.value).length===0?(b(),c("div",it,r[13]||(r[13]=[t("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[t("p",{class:"text-gray-600 text-base sm:text-lg"},"No DR2 List Available")],-1)]))):(b(!0),c(m,{key:2},R(_.value,e=>(b(),c("div",{key:e,class:"mb-8"},[t("h2",ct,l(e),1),t("div",gt,[t("table",Et,[r[41]||(r[41]=t("tr",null,[t("td",{colspan:"6",class:"text-center font-bold text-lg border border-gray-400 p-1"},[n(" MALIWALO"),t("br"),n(" TARLAC CITY ")])],-1)),t("tr",null,[r[16]||(r[16]=t("td",{colspan:"4",class:"font-bold border border-gray-400 p-1"},[n("DELIVERY GOODS RECEIPT"),t("br"),n("BW PRODUCT")],-1)),t("td",Tt,[r[14]||(r[14]=n(" DR #: ")),t("span",Ct,l(a.value[e][0].JOURNALID),1),r[15]||(r[15]=t("br",null,null,-1)),n(" DELIVERY DATE: "+l(f(a.value[e][0].DELIVERYDATE)),1)])]),t("tr",null,[r[19]||(r[19]=t("td",{colspan:"3",class:"border border-gray-400 p-1"},[n("RECEIVED FROM:"),t("br"),t("span",{class:"font-bold"},"HEADOFFICE")],-1)),t("td",ft,[r[17]||(r[17]=n("DELIVERED TO:")),r[18]||(r[18]=t("br",null,null,-1)),t("span",yt,l(e),1)])]),r[42]||(r[42]=t("tr",{class:"bg-gray-200 font-bold"},[t("td",{class:"border border-gray-400 p-1"},"PRODUCT DESCRIPTION"),t("td",{class:"border border-gray-400 p-1 text-center"},"DELIVERED QUANTITY"),t("td",{class:"border border-gray-400 p-1 text-center"},"RECEIVED QUANTITY"),t("td",{class:"border border-gray-400 p-1 text-center"},"VARIANCE"),t("td",{class:"border border-gray-400 p-1 text-right"},"UNIT COST"),t("td",{class:"border border-gray-400 p-1 text-right"},"TOTAL")],-1)),(b(!0),c(m,null,R(a.value[e],o=>(b(),c("tr",{key:o.ITEMID},[t("td",Dt,l(o.ITEMNAME),1),t("td",At,l(o.CHECKINGCOUNT),1),r[20]||(r[20]=t("td",{class:"border border-gray-400 p-1 text-center"},"0",-1)),t("td",xt,l(o.actual-o.CHECKINGCOUNT),1),t("td",mt,l(i(o.COST)),1),t("td",Ot,l(i(o.COST*o.CHECKINGCOUNT)),1)]))),128)),t("tr",null,[r[21]||(r[21]=t("td",{colspan:"5",class:"border border-gray-400 p-1 text-right font-bold"},"TOTAL",-1)),t("td",vt,l(i(L(a.value[e]))),1)]),e===""&&C.sptrans&&C.sptrans.length>0?(b(),c(m,{key:0},[r[25]||(r[25]=t("tr",null,[t("td",{colspan:"6",class:"border border-gray-400 p-1 font-bold"},"SPECIAL ORDERS")],-1)),r[26]||(r[26]=t("tr",null,[t("td",{class:"border border-gray-400 p-1"},"PROMO"),t("td",{class:"border border-gray-400 p-1 text-center"},"DELIVERED QUANTITY"),t("td",{class:"border border-gray-400 p-1 text-center"},"RECEIVED QUANTITY"),t("td",{class:"border border-gray-400 p-1 text-center"},"VARIANCE"),t("td",{class:"border border-gray-400 p-1 text-right"},"UNIT COST"),t("td",{class:"border border-gray-400 p-1 text-right"},"AMOUNT")],-1)),(b(!0),c(m,null,R(C.sptrans,(o,S)=>(b(),c("tr",{key:S},[t("td",Rt,l(o.ITEMNAME),1),t("td",It,l(o.COUNTED),1),r[22]||(r[22]=t("td",{class:"border border-gray-400 p-1 text-center"},"0",-1)),r[23]||(r[23]=t("td",{class:"border border-gray-400 p-1 text-center"},"0",-1)),t("td",St,l(i(o.COST)),1),t("td",Nt,l(i(o.COST*o.COUNTED)),1)]))),128)),t("tr",null,[r[24]||(r[24]=t("td",{colspan:"5",class:"border border-gray-400 p-1 text-right font-bold"},"TOTAL",-1)),t("td",ht,l(i(Y(C.sptrans))),1)])],64)):h("",!0),t("tr",null,[t("td",Lt,[r[27]||(r[27]=n(" ENDORSED BY:DISPATCHING")),r[28]||(r[28]=t("br",null,null,-1)),t("span",Ut,l(a.value[e][0].DISPATCHER)+" | "+l(f(a.value[e][0].DELIVERYDATE)),1),r[29]||(r[29]=t("br",null,null,-1)),r[30]||(r[30]=n(" BREADS/CAKES")),r[31]||(r[31]=t("br",null,null,-1)),r[32]||(r[32]=n(" NAME & SIGNATURE/ DATE "))]),t("td",wt,[t("span",Vt,l(a.value[e][0].LOGISTICS)+" | "+l(f(a.value[e][0].DELIVERYDATE)),1),r[33]||(r[33]=t("br",null,null,-1)),r[34]||(r[34]=n(" DELIVERED BY:LOGISTICS")),r[35]||(r[35]=t("br",null,null,-1)),r[36]||(r[36]=n(" NAME & SIGNATURE/ DATE "))])]),r[43]||(r[43]=t("tr",null,[t("td",{colspan:"6",class:"border border-gray-400 p-1 font-bold"},"CRATES QUANTITY DELIVERED")],-1)),t("tr",null,[t("td",$t,[t("div",kt,[r[37]||(r[37]=t("span",null,"ORANGE CRATES",-1)),t("span",Mt,l(a.value[e][0].orangeCrates),1),A(t("input",{type:"number","onUpdate:modelValue":r[3]||(r[3]=o=>u.value.orangeCrates=o),onChange:o=>v(e,a.value[e][0].JOURNALID),class:"w-16 px-2 py-1 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"},null,40,Pt),[[O,u.value.orangeCrates]])])]),t("td",_t,[t("div",Yt,[r[38]||(r[38]=t("span",null,"BLUE CRATES",-1)),t("span",Ht,l(a.value[e][0].blueCrates),1),A(t("input",{type:"number","onUpdate:modelValue":r[4]||(r[4]=o=>u.value.blueCrates=o),onChange:o=>v(e,a.value[e][0].JOURNALID),class:"w-16 px-2 py-1 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"},null,40,Gt),[[O,u.value.blueCrates]])])]),t("td",Bt,[t("div",jt,[r[39]||(r[39]=t("span",null,"EMPANADA CRATES",-1)),t("span",Ft,l(a.value[e][0].empanadaCrates),1),A(t("input",{type:"number","onUpdate:modelValue":r[5]||(r[5]=o=>u.value.empanadaCrates=o),onChange:o=>v(e,a.value[e][0].JOURNALID),class:"w-16 px-2 py-1 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"},null,40,Kt),[[O,u.value.empanadaCrates]])])]),t("td",Qt,[t("div",zt,[r[40]||(r[40]=t("span",null,"BOX",-1)),t("span",Jt,l(a.value[e][0].box),1),A(t("input",{type:"number","onUpdate:modelValue":r[6]||(r[6]=o=>u.value.box=o),onChange:o=>v(e,a.value[e][0].JOURNALID),class:"w-16 px-2 py-1 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"},null,40,qt),[[O,u.value.box]])])])])])])]))),128))])])]),_:1})}}},cr=tt(Wt,[["__scopeId","data-v-551967b4"]]);export{cr as default};
