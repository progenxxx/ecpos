import{Q as L,d as r,s as _,c as p,w as u,o as a,f as T,a as e,b as I,g as w,e as i,t as l,F as M,h as $,j as U,v as V}from"./app-Do8mhcc_.js";import{_ as G,a as F}from"./Update-CGm56EGF.js";import{_ as E}from"./Modal.vue_vue_type_style_index_0_scoped_fd08cd24_lang-Cw8u42Sx.js";import{_ as B}from"./AdminPanel-B-hw9tyY.js";import"./Modal-BufSFMTr.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";import"./InputError-Bl4xi_3e.js";import"./FormComponent-DbpXKqv4.js";import"./RetailGroup-D_6SfRWw.js";import"./Logout-MAsyIaFu.js";/* empty css                                                             */import"./RetailItems-SitUjnLk.js";import"./Attendance-Bi3B0c_5.js";const j={class:"absolute adjust"},H={class:"flex justify-start items-center"},J={role:"tablist",class:"tabs tabs-lifted mt-10 p-5"},K={role:"tabpanel",class:"tab-content bg-base-200 border-base-300 p-6 h-[70vh] overflow-y-auto"},Y={class:"container mx-auto px-4"},z={class:"flex flex-wrap -mx-4"},Q={key:0,class:"col-span-full text-center mt-8"},q={key:1,class:"col-span-full text-center mt-8"},W={class:"text-red-600 text-lg"},X={key:2,class:"col-span-full text-center mt-8"},Z={class:"bg-blue-400 text-white text-center py-1"},tt={class:"w-full px-4 mb-8"},et={class:"flex bg-gray-200 font-semibold"},st={class:"w-1/4 p-2 text-center border-r border-gray-400"},ot={class:"divide-y divide-gray-300"},rt={class:"w-1/2 p-2 border-r border-gray-300"},at={class:"w-1/4 p-2 text-center border-r border-gray-300"},lt={class:"w-1/4 p-2 text-center"},dt=["onUpdate:modelValue","onInput"],nt={class:"flex bg-red-200"},it={class:"w-1/4 p-2 text-center border-r border-gray-300"},ct={class:"w-1/4 p-2 text-center"},bt={class:"max-w-md mx-auto border border-gray-300"},ut={class:"w-full"},pt={class:"border-b border-l border-gray-300 p-2 text-sm text-right"},gt={class:"border-l border-gray-300 p-2 text-sm text-right"},Rt={__name:"Index copy",setup(vt){const h=L(),d=r(h.props.picklist.map(s=>({...s,actual:s.COUNTED}))),N=r(""),C=r(""),D=r(""),g=r(!1),v=r(!1),R=r(!1),m=r(null),A=()=>{g.value=!1},S=()=>{v.value=!1},x=_(()=>d.value.reduce((s,t)=>s+parseFloat(t.COUNTED||0),0)),O=_(()=>d.value.reduce((s,t)=>s+parseFloat(t.actual||0),0)),n=()=>{const s=new Date;return`${s.getMonth()+1}/${s.getDate()}/${s.getFullYear().toString().substr(-2)}`},c=s=>{const t=parseFloat(s);return Number.isInteger(t)?t.toString():Math.round(t).toString()},k=s=>{if(s.actual!==""){const t=parseFloat(s.actual);s.actual=Math.round(t).toString()}},P=r(null),f=()=>{var b;const s=window.open("","","left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0"),t=d.value.map(o=>`
    <tr>
      <td class="border p-2">${o.ITEMNAME}</td>
      <td class="border p-2 text-center">${c(o.COUNTED)}</td>
    </tr>
  `).join("");s.document.write(`
    <html>
      <head>
        <title>Packing List</title>
        <style>
          @page { size: A4 portrait; }
          body { font-family: Arial, sans-serif; }
          .container { width: 50%; float: left; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid black; padding: 4px; font-size: 12px; }
          .text-center { text-align: center; }
          .bg-blue-800 { background-color: #2b6cb0; color: white; text-align: center; padding: 4px 0; font-weight: bold; }
          .bg-blue-600 { background-color: #3182ce; color: white; text-align: center; padding: 2px 0; font-weight: 600; }
          .bg-blue-400 { background-color: #4299e1; color: white; text-align: center; padding: 2px 0; }
          .bg-red-200 { background-color: #fed7d7; }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST</div>
          <div class="bg-blue-400">DELIVERY DATE: ${n()}</div>
          <table>
            <thead>
              <tr>
                <th class="border p-2">PRODUCT</th>
                <th class="border p-2">${((b=d.value[0])==null?void 0:b.STORENAME)||"STORE"}</th>
              </tr>
            </thead>
            <tbody>
              ${t}
              <tr class="bg-red-200">
                <td class="border p-2 font-bold">TOTAL</td>
                <td class="border p-2 text-center font-bold">${c(x.value)}</td>
              </tr>
              <tr>
                <td>
                  <div>DISPATCHER: SIGN OVER PRINTED NAME</div>
                  <div style="border-bottom: 1px solid #ccc; margin-top: 16px;"></div>
                </td>
                <td class="text-sm text-right">${n()}</td>
              </tr>
              <tr>
                <td>
                  <div>LOGISTICS: SIGN OVER PRINTED NAME</div>
                  <div style="border-bottom: 1px solid #ccc; margin-top: 16px;"></div>
                </td>
                <td class="text-sm text-right">${n()}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </body>
    </html>
  `),s.document.close(),s.focus(),s.print(),s.close()};return(s,t)=>(a(),p(B,{"active-tab":"PICKLIST"},{modals:u(()=>[v.value?(a(),p(G,{key:0,onToggleActive:S})):T("",!0),g.value?(a(),p(F,{key:1,ID:N.value,SUBJECT:C.value,DESCRIPTION:D.value,onToggleActive:A},null,8,["ID","SUBJECT","DESCRIPTION"])):T("",!0)]),main:u(()=>{var b;return[e("div",j,[e("div",H,[I(E,{type:"button",onClick:f,class:"m-6 bg-navy"},{default:u(()=>t[0]||(t[0]=[w(" PRINT PL ")])),_:1}),I(E,{type:"button",onClick:f,class:"bg-navy"},{default:u(()=>t[1]||(t[1]=[w(" PRINT DR ")])),_:1})])]),e("div",J,[t[13]||(t[13]=e("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab bg-base-200 border-base-300","aria-label":"PICK LIST",checked:""},null,-1)),e("div",K,[e("div",Y,[e("div",z,[R.value?(a(),i("div",Q,t[2]||(t[2]=[e("p",{class:"text-gray-600 text-lg"},"Loading...",-1)]))):m.value?(a(),i("div",q,[e("p",W,l(m.value),1)])):!d.value||d.value.length===0?(a(),i("div",X,t[3]||(t[3]=[e("div",{class:"bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto"},[e("p",{class:"text-gray-600 text-base sm:text-lg"},"No Pick List Available")],-1)]))):(a(),i("div",{key:3,class:"max-w-xl mx-auto bg-white shadow-lg",ref_key:"printableContent",ref:P},[t[11]||(t[11]=e("div",{class:"bg-blue-800 text-white text-center py-2 font-bold"}," ELJIN CORPORATION ",-1)),t[12]||(t[12]=e("div",{class:"bg-blue-600 text-white text-center py-1 font-semibold"}," PACKING LIST ",-1)),e("div",Z," DELIVERY DATE: "+l(n()),1),e("div",tt,[e("div",et,[t[4]||(t[4]=e("div",{class:"w-1/2 p-2 border-r border-gray-400"},"PRODUCT",-1)),e("div",st,l(((b=d.value[0])==null?void 0:b.STORENAME)||"STORE"),1),t[5]||(t[5]=e("div",{class:"w-1/4 p-2 text-center"},"ACTUAL",-1))]),e("div",ot,[(a(!0),i(M,null,$(d.value,o=>(a(),i("div",{key:o.ITEMID,class:"flex"},[e("div",rt,l(o.ITEMNAME),1),e("div",at,l(c(o.COUNTED)),1),e("div",lt,[U(e("input",{"onUpdate:modelValue":y=>o.actual=y,type:"number",class:"w-full text-center border border-gray-300 rounded",onInput:y=>k(o)},null,40,dt),[[V,o.actual]])])]))),128)),e("div",nt,[t[6]||(t[6]=e("div",{class:"w-1/2 p-2 border-r border-gray-300"},"TOTAL",-1)),e("div",it,l(c(x.value)),1),e("div",ct,l(c(O.value)),1)])])]),e("div",bt,[e("table",ut,[e("tr",null,[t[7]||(t[7]=e("td",{class:"border-b border-r border-gray-300 p-2 text-red-600 font-semibold"},"DISPATCHER:",-1)),t[8]||(t[8]=e("td",{class:"border-b border-gray-300 p-2"},[e("div",null,"SIGN OVER PRINTED NAME"),e("div",{class:"border-b border-gray-300 mt-4"})],-1)),e("td",pt,l(n()),1)]),e("tr",null,[t[9]||(t[9]=e("td",{class:"border-r border-gray-300 p-2 font-semibold"},"LOGISTICS:",-1)),t[10]||(t[10]=e("td",{class:"p-2"},[e("div",null,"SIGN OVER PRINTED NAME"),e("div",{class:"border-b border-gray-300 mt-4"})],-1)),e("td",gt,l(n()),1)])])])],512))])])]),t[14]||(t[14]=e("input",{type:"radio",name:"my_tabs_2",role:"tab",class:"tab","aria-label":"DR"},null,-1)),t[15]||(t[15]=e("div",{role:"tabpanel",class:"tab-content bg-base-100 border-base-200 p-6"}," DR ",-1))])]}),_:1}))}};export{Rt as default};
